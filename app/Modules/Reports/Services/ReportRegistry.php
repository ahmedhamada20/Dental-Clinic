<?php

namespace App\Modules\Reports\Services;

use App\Modules\Reports\DTOs\ReportFilterDTO;
use InvalidArgumentException;

class ReportRegistry
{
    public function __construct(
        private readonly AppointmentReportService $appointmentReportService,
        private readonly RevenueReportService $revenueReportService,
        private readonly InvoiceReportService $invoiceReportService,
        private readonly PatientReportService $patientReportService,
        private readonly ServiceReportService $serviceReportService,
        private readonly PromotionReportService $promotionReportService,
        private readonly DoctorReportService $doctorReportService,
        private readonly AuditLogReportService $auditLogReportService,
    ) {
    }

    public function generate(string $reportType, ReportFilterDTO $filters): array
    {
        return $this->serviceFor($reportType)->generate($filters);
    }

    public function serviceFor(string $reportType): object
    {
        return match ($reportType) {
            'appointments' => $this->appointmentReportService,
            'revenue' => $this->revenueReportService,
            'invoices' => $this->invoiceReportService,
            'patients' => $this->patientReportService,
            'services' => $this->serviceReportService,
            'promotions' => $this->promotionReportService,
            'doctors' => $this->doctorReportService,
            'audit_logs' => $this->auditLogReportService,
            default => throw new InvalidArgumentException("Unsupported report type [{$reportType}]."),
        };
    }

    public function supportedTypes(): array
    {
        return ['appointments', 'revenue', 'invoices', 'patients', 'services', 'promotions', 'doctors', 'audit_logs'];
    }
}

