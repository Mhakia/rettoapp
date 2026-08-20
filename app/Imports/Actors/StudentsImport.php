<?php

namespace App\Imports\Actors;

use App\Models\Group;
use App\Models\ImportBatch;
use App\Models\Institution;
use App\Models\InstitutionMembership;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
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
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;

/**
 * Reads one row at a time (chunkSize 1) so the DB-backed unique rule also catches duplicates within the same file:
 * each row is fully created (or rejected) before the next one is validated.
 */
class StudentsImport implements SkipsEmptyRows, SkipsOnFailure, ToModel, WithChunkReading, WithHeadingRow, WithValidation
{
    use RemembersRowNumber, SkipsFailures;

    /**
     * Accepted short codes for the "Tipo de documento" column, mapped to the enum stored on students.document_type.
     */
    private const DOCUMENT_TYPES = [
        'TI' => 'tarjeta_identidad',
        'RC' => 'registro_civil',
    ];

    public int $created = 0;

    /**
     * @var array<int, array{row: int, message: string}>
     */
    public array $creationErrors = [];

    public function __construct(private readonly Institution $institution, private readonly ?ImportBatch $importBatch = null) {}

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
            $documentNumber = preg_replace('/\D/', '', (string) $row['numero_de_documento']);
            $firstName = $this->sanitize($row['nombres']);
            $lastName = $this->sanitize($row['apellidos']);
            $birthDate = $this->parseDate($row['fecha_de_nacimiento']);
            $groupId = Group::where('institution_id', $this->institution->id)
                ->where('name', trim((string) $row['salon_o_grupo']))
                ->value('id');

            DB::transaction(function () use ($documentType, $documentNumber, $firstName, $lastName, $birthDate, $groupId) {
                $user = User::create([
                    'name' => trim("{$firstName} {$lastName}"),
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    // No login flow yet for students: placeholder email so the account can be wired up later.
                    // Prefixed with document_type too: document_number alone isn't unique across types (only the type+number pair is).
                    'email' => "{$documentType}-{$documentNumber}@students.tereto.local",
                    'password' => Hash::make(Str::random(32)),
                ]);
                $user->assignRole('student');

                $student = Student::create([
                    'user_id' => $user->id,
                    'document_type' => $documentType,
                    'document_number' => $documentNumber,
                    'birth_date' => $birthDate,
                    'import_batch_id' => $this->importBatch?->id,
                ]);

                InstitutionMembership::create([
                    'user_id' => $student->user_id,
                    'institution_id' => $this->institution->id,
                    'group_id' => $groupId,
                    'status' => 'active',
                    'started_at' => now(),
                ]);
            });

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
                    $fail(__('Usa TI o RC.'));
                }
            }],
            'numero_de_documento' => ['required', 'regex:/^[0-9]{5,20}$/'],
            'fecha_de_nacimiento' => [function ($attribute, $value, $fail) {
                $date = $this->parseDate($value);

                if (! $date || $date->isAfter(now())) {
                    $fail(__('Fecha de nacimiento inválida.'));
                }
            }],
            'salon_o_grupo' => ['required', function ($attribute, $value, $fail) {
                $exists = Group::where('institution_id', $this->institution->id)
                    ->where('name', trim((string) $value))
                    ->exists();

                if (! $exists) {
                    $fail(__('El salón o grupo ":name" no existe en tu institución.', ['name' => trim((string) $value)]));
                }
            }],
        ];
    }

    /**
     * Cross-field check: no two students (existing or within this same file) may share document_type + document_number.
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

                if (Student::where('document_type', $documentType)->where('document_number', $documentNumber)->exists()) {
                    $validator->errors()->add("{$rowIndex}.numero_de_documento", __('Ya existe un estudiante con ese tipo y número de documento.'));
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
            'fecha_de_nacimiento' => __('fecha de nacimiento'),
            'salon_o_grupo' => __('salón o grupo'),
        ];
    }

    private function parseDate(mixed $value): ?Carbon
    {
        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value);
        }

        if (is_numeric($value)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value));
            } catch (Throwable) {
                return null;
            }
        }

        foreach (['d/m/Y', 'Y-m-d', 'd-m-Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, trim((string) $value));
            } catch (Throwable) {
                continue;
            }
        }

        return null;
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
