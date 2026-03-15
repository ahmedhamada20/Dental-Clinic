<?php

namespace App\Modules\Reports\Services;

use App\Modules\Reports\DTOs\ReportFilterDTO;
use App\Modules\Reports\Exports\ReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportExportService
{
    public function __construct(private readonly ReportRegistry $reportRegistry)
    {
    }

    public function exportPdf(string $reportType, ReportFilterDTO $filters): Response
    {
        $report = $this->reportRegistry->generate($reportType, $filters);
        $pdf = Pdf::loadView('admin.reports.export', [
            'reportType' => $reportType,
            'report' => $report,
        ])->setPaper('a4', 'landscape');

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $this->filename($reportType, 'pdf') . '"',
        ]);
    }

    public function exportExcel(string $reportType, ReportFilterDTO $filters): BinaryFileResponse
    {
        $report = $this->reportRegistry->generate($reportType, $filters);
        $rows = $this->normalizeRows($report['rows'] ?? []);
        $headings = array_keys($rows[0] ?? ['message' => 'No rows available']);

        return Excel::download(
            new ReportExport($this->title($reportType), $rows ?: [['message' => 'No rows available']], $headings),
            $this->filename($reportType, 'xlsx')
        );
    }

    private function normalizeRows(array $rows): array
    {
        return collect($rows)
            ->map(function ($row) {
                return collect($row)
                    ->map(fn ($value) => is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value)
                    ->all();
            })
            ->values()
            ->all();
    }

    private function title(string $reportType): string
    {
        return Str::headline(str_replace('_', ' ', $reportType)) . ' Report';
    }

    private function filename(string $reportType, string $extension): string
    {
        return Str::slug($reportType . '-report-' . now()->format('Y-m-d-His')) . '.' . $extension;
    }
}

