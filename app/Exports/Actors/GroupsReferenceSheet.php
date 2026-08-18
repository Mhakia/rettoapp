<?php

namespace App\Exports\Actors;

use App\Models\Group;
use App\Models\Institution;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Reference sheet listing the exact group names accepted by the "Grupos"/"Salón o grupo" columns.
 */
class GroupsReferenceSheet implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    public function __construct(private readonly Institution $institution) {}

    /**
     * @return Collection<int, array<int, string>>
     */
    public function collection(): Collection
    {
        return Group::where('institution_id', $this->institution->id)
            ->orderBy('name')
            ->pluck('name')
            ->map(fn (string $name) => [$name]);
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Grupos disponibles en tu institución'];
    }

    public function title(): string
    {
        return 'Grupos disponibles';
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
