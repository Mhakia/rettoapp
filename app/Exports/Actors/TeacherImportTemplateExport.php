<?php

namespace App\Exports\Actors;

use App\Models\Institution;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class TeacherImportTemplateExport implements Export, WithMultipleSheets
{
    public function __construct(private readonly Institution $institution) {}

    /**
     * @return array<int, object>
     */
    public function sheets(): array
    {
        return [
            new TeacherTemplateSheet,
            new GroupsReferenceSheet($this->institution),
        ];
    }
}
