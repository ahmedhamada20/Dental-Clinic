<?php

namespace App\Modules\Reports\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ReportExport implements FromArray, ShouldAutoSize, WithHeadings, WithTitle
{
    public function __construct(
        private readonly string $title,
        private readonly array $rows,
        private readonly array $headings,
    ) {
    }

    public function array(): array
    {
        return array_map(function (array $row) {
            return collect($this->headings)
                ->map(fn ($heading) => $row[$heading] ?? null)
                ->all();
        }, $this->rows);
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function title(): string
    {
        return str($this->title)->limit(31, '')->toString();
    }
}

