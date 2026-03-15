<?php

namespace App\Modules\Reports\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Reports\DTOs\ReportFilterDTO;
use App\Modules\Reports\Requests\ReportFilterRequest;
use App\Modules\Reports\Resources\ReportCollectionResource;
use App\Modules\Reports\Services\ReportExportService;
use App\Modules\Reports\Services\ReportRegistry;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportRegistry $reportRegistry,
        private readonly ReportExportService $reportExportService,
    ) {
    }

    public function appointments(ReportFilterRequest $request): JsonResponse
    {
        return $this->respond('appointments', $request, 'Appointments report generated.');
    }

    public function revenue(ReportFilterRequest $request): JsonResponse
    {
        return $this->respond('revenue', $request, 'Revenue report generated.');
    }

    public function invoices(ReportFilterRequest $request): JsonResponse
    {
        return $this->respond('invoices', $request, 'Invoices report generated.');
    }

    public function patients(ReportFilterRequest $request): JsonResponse
    {
        return $this->respond('patients', $request, 'Patients report generated.');
    }

    public function services(ReportFilterRequest $request): JsonResponse
    {
        return $this->respond('services', $request, 'Services report generated.');
    }

    public function promotions(ReportFilterRequest $request): JsonResponse
    {
        return $this->respond('promotions', $request, 'Promotions report generated.');
    }

    public function doctors(ReportFilterRequest $request): JsonResponse
    {
        return $this->respond('doctors', $request, 'Doctors report generated.');
    }

    public function auditLogs(ReportFilterRequest $request): JsonResponse
    {
        return $this->respond('audit_logs', $request, 'Audit logs report generated.');
    }

    public function exportPdf(ReportFilterRequest $request, string $reportType): Response
    {
        return $this->reportExportService->exportPdf($reportType, ReportFilterDTO::fromArray($request->validated() + ['report_type' => $reportType]));
    }

    public function exportExcel(ReportFilterRequest $request, string $reportType): BinaryFileResponse
    {
        return $this->reportExportService->exportExcel($reportType, ReportFilterDTO::fromArray($request->validated() + ['report_type' => $reportType]));
    }

    private function respond(string $reportType, ReportFilterRequest $request, string $message): JsonResponse
    {
        $data = $this->reportRegistry->generate($reportType, ReportFilterDTO::fromArray($request->validated() + ['report_type' => $reportType]));

        return ApiResponse::success(new ReportCollectionResource($data), $message);
    }
}
