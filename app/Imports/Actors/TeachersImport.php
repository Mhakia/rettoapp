<?php

namespace App\Imports\Actors;

use App\Models\Group;
use App\Models\Institution;
use App\Models\InstitutionMembership;
use App\Models\User;
use App\Notifications\TeacherAccountCreated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Throwable;

/**
 * Reads one row at a time (chunkSize 1) so the DB-backed unique rules also catch duplicates within the same file:
 * each row is fully created (or rejected) before the next one is validated.
 */
class TeachersImport implements SkipsEmptyRows, SkipsOnFailure, ToModel, WithChunkReading, WithHeadingRow, WithValidation
{
    use RemembersRowNumber, SkipsFailures;

    /**
     * Accepted short codes for the "Tipo de documento" column, mapped to the enum stored on users.document_type.
     */
    private const DOCUMENT_TYPES = [
        'CC' => 'cedula_ciudadania',
        'CE' => 'cedula_extranjeria',
        'PA' => 'pasaporte',
    ];

    public int $created = 0;

    /**
     * @var array<int, array{row: int, message: string}>
     */
    public array $creationErrors = [];

    public function __construct(private readonly Institution $institution) {}

    public function chunkSize(): int
    {
        return 1;
    }

    /**
     * A row is only considered real data if it has at least a name or a document number: stray
     * whitespace/formatting left over in other columns (common after editing the template in Excel)
     * shouldn't turn a blank row into a pile of "required" errors.
     */
    public function isEmptyWhen(array $row): bool
    {
        return trim((string) ($row['nombres'] ?? '')) === ''
            && trim((string) ($row['apellidos'] ?? '')) === ''
            && trim((string) ($row['numero_de_documento'] ?? '')) === '';
    }

    public function model(array $row): Model|array|null
    {
        try {
            $documentType = self::DOCUMENT_TYPES[strtoupper(trim((string) $row['tipo_de_documento']))];
            $firstName = $this->sanitize($row['nombres']);
            $lastName = $this->sanitize($row['apellidos']);

            $teacher = DB::transaction(function () use ($row, $documentType, $firstName, $lastName) {
                $teacher = User::create([
                    'name' => trim("{$firstName} {$lastName}"),
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'document_type' => $documentType,
                    'document_number' => preg_replace('/\D/', '', (string) $row['numero_de_documento']),
                    'phone' => trim((string) $row['celular']),
                    'email' => trim((string) $row['correo']),
                    'password' => Hash::make(Str::random(32)),
                ]);
                $teacher->assignRole('teacher');

                $membership = InstitutionMembership::create([
                    'user_id' => $teacher->id,
                    'institution_id' => $this->institution->id,
                    'status' => 'active',
                    'started_at' => now(),
                ]);

                $groupIds = Group::where('institution_id', $this->institution->id)
                    ->whereIn('name', $this->parseGroupNames($row['grupos'] ?? null))
                    ->pluck('id');

                if ($groupIds->isNotEmpty()) {
                    $teacher->teacherGroups()->attach($groupIds, ['institution_membership_id' => $membership->id]);
                }

                return $teacher;
            });

            // Dispatched after the transaction commits so the queued job always finds the row.
            $teacher->notify(new TeacherAccountCreated);

            $this->created++;
        } catch (Throwable $e) {
            $this->creationErrors[] = ['row' => $this->getRowNumber() ?? 0, 'message' => $e->getMessage()];
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombres' => ['required', 'string', 'max:255'],
            'apellidos' => ['required', 'string', 'max:255'],
            'tipo_de_documento' => ['required', function ($attribute, $value, $fail) {
                if (! array_key_exists(strtoupper(trim((string) $value)), self::DOCUMENT_TYPES)) {
                    $fail(__('Usa CC, CE o PA.'));
                }
            }],
            'numero_de_documento' => ['required', 'regex:/^[0-9]{5,20}$/'],
            'celular' => ['required', 'regex:/^[0-9+ ]{7,20}$/'],
            'correo' => ['required', 'email', 'max:255', 'unique:users,email'],
            'grupos' => [function ($attribute, $value, $fail) {
                $missing = $this->parseGroupNames($value)->diff(
                    Group::where('institution_id', $this->institution->id)->pluck('name')
                );

                if ($missing->isNotEmpty()) {
                    $fail(__('Grupos no encontrados: :names', ['names' => $missing->implode(', ')]));
                }
            }],
        ];
    }

    /**
     * Cross-field check: no two teachers (existing or within this same file) may share document_type + document_number.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            foreach ($validator->getData() as $rowIndex => $row) {
                $documentType = self::DOCUMENT_TYPES[strtoupper(trim((string) ($row['tipo_de_documento'] ?? '')))] ?? null;
                $documentNumber = preg_replace('/\D/', '', (string) ($row['numero_de_documento'] ?? ''));

                if (! $documentType || $documentNumber === '') {
                    continue;
                }

                if (User::where('document_type', $documentType)->where('document_number', $documentNumber)->exists()) {
                    $validator->errors()->add("{$rowIndex}.numero_de_documento", __('Ya existe un profesor con ese tipo y número de documento.'));
                }
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function customValidationAttributes(): array
    {
        return [
            'nombres' => __('nombres'),
            'apellidos' => __('apellidos'),
            'tipo_de_documento' => __('tipo de documento'),
            'numero_de_documento' => __('número de documento'),
            'celular' => __('celular'),
            'correo' => __('correo'),
            'grupos' => __('grupos'),
        ];
    }

    /**
     * @return Collection<int, string>
     */
    private function parseGroupNames(?string $value): Collection
    {
        return collect(explode(',', (string) $value))
            ->map(fn ($name) => trim($name))
            ->filter()
            ->values();
    }

    /**
     * Strip tags and neutralize a leading formula/CSV-injection character before persisting free-text input.
     */
    private function sanitize(mixed $value): string
    {
        $value = trim(strip_tags((string) $value));

        if ($value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            $value = "'".$value;
        }

        return $value;
    }
}
