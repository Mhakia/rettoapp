<?php

namespace App\Livewire\Actors;

use App\Exports\Actors\StudentImportTemplateExport;
use App\Imports\Actors\StudentsImport;
use App\Models\ImportBatch;
use App\Models\Institution;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

#[Title('Carga masiva de estudiantes')]
class ImportStudents extends Component
{
    /**
     * Rows above this are rejected upfront: keeps the request within a reasonable time budget.
     */
    private const MAX_ROWS = 500;

    use WithFileUploads;

    /**
     * Institution to import students into: the query string is used by staff (pedagogue/manager/
     * super_admin) browsing a specific institution; institution_admin always uses their own, ignoring it.
     */
    #[Url(as: 'institution')]
    public ?string $institutionUuid = null;

    public int $institutionId;

    public string $institutionName = '';

    public string $backUrl = '';

    public $file = null;

    public ?int $createdCount = null;

    /**
     * @var array<int, array{row: int, message: string}>
     */
    public array $rowErrors = [];

    public function mount(): void
    {
        $institution = $this->institutionUuid
            ? Institution::where('uuid', $this->institutionUuid)->firstOrFail()
            : Auth::user()->institution;

        abort_unless($institution, 403);
        $this->authorize('manageActors', $institution);

        $this->institutionId = $institution->id;
        $this->institutionName = $institution->name;
        $this->institutionUuid = $institution->uuid;

        $this->backUrl = Auth::user()->hasRole('institution_admin')
            ? route('actors.students.index')
            : route('directory.students', ['institution' => $institution->uuid]);
    }

    public function downloadTemplate()
    {
        $institution = Institution::findOrFail($this->institutionId);

        return Excel::download(new StudentImportTemplateExport($institution), 'plantilla-estudiantes.xlsx');
    }

    public function import(): void
    {
        $this->createdCount = null;
        $this->rowErrors = [];

        $this->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ]);

        $rows = Excel::toCollection(null, $this->file->getRealPath());
        $dataRowCount = max(($rows->first()?->count() ?? 0) - 1, 0);

        if ($dataRowCount < 1) {
            $this->addError('file', __('El archivo no tiene filas de datos (solo encabezados o está vacío).'));

            return;
        }

        if ($dataRowCount > self::MAX_ROWS) {
            $this->addError('file', __('El archivo tiene :count filas de datos; el máximo permitido por carga es :max.', [
                'count' => $dataRowCount,
                'max' => self::MAX_ROWS,
            ]));

            return;
        }

        set_time_limit(120);

        $institution = Institution::findOrFail($this->institutionId);

        $importBatch = ImportBatch::create([
            'uploaded_by' => Auth::id(),
            'institution_id' => $institution->id,
            'type' => 'students',
            'original_filename' => $this->file->getClientOriginalName(),
            'file_hash' => hash_file('sha256', $this->file->getRealPath()),
            'total_rows' => $dataRowCount,
        ]);

        $import = new StudentsImport($institution, $importBatch);
        Excel::import($import, $this->file);

        $this->createdCount = $import->created;
        $this->rowErrors = collect($import->failures())
            ->map(fn ($failure) => ['row' => $failure->row(), 'message' => implode(' ', $failure->errors())])
            ->concat($import->creationErrors)
            ->sortBy('row')
            ->values()
            ->all();

        $importBatch->update([
            'success_count' => $this->createdCount,
            'error_count' => count($this->rowErrors),
        ]);

        $this->reset('file');

        if ($this->createdCount > 0) {
            Flux::toast(variant: 'success', text: __(':count estudiantes creados.', ['count' => $this->createdCount]));
        }
    }

    public function render()
    {
        return view('livewire.actors.import-students');
    }
}
