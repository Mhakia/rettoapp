<?php

namespace App\Exports\Actors;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentTemplateSheet implements FromArray, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    /**
     * @return array<int, array<int, string>>
     */
    public function array(): array
    {
        return [
            ['Ana', 'Gómez', 'TI', '1122334455', '01/01/2015', '1°A'],
        ];
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Nombres', 'Apellidos', 'Tipo de documento', 'Numero de documento', 'Fecha de nacimiento', 'Salon o grupo'];
    }

    public function title(): string
    {
        return 'Estudiantes';
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
