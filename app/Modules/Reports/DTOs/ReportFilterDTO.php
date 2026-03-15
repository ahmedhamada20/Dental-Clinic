<?php

namespace App\Modules\Reports\DTOs;

final class ReportFilterDTO
{
    public function __construct(
        public readonly ?string $fromDate,
        public readonly ?string $toDate,
        public readonly ?int $doctorId,
        public readonly ?int $serviceId,
        public readonly ?int $specialtyId,
        public readonly ?string $groupBy,
        public readonly ?string $reportType,
        public readonly ?string $exportFormat,
        public readonly ?string $status,
        public readonly ?string $invoiceStatus
    ) {
    }

    public static function fromArray(array $validated): self
    {
        return new self(
            fromDate: $validated['from_date'] ?? null,
            toDate: $validated['to_date'] ?? null,
            doctorId: isset($validated['doctor_id']) ? (int) $validated['doctor_id'] : null,
            serviceId: isset($validated['service_id']) ? (int) $validated['service_id'] : null,
            specialtyId: isset($validated['specialty_id']) ? (int) $validated['specialty_id'] : null,
            groupBy: $validated['group_by'] ?? null,
            reportType: $validated['report_type'] ?? null,
            exportFormat: $validated['export_format'] ?? null,
            status: $validated['status'] ?? null,
            invoiceStatus: $validated['invoice_status'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'from_date' => $this->fromDate,
            'to_date' => $this->toDate,
            'doctor_id' => $this->doctorId,
            'service_id' => $this->serviceId,
            'specialty_id' => $this->specialtyId,
            'group_by' => $this->groupBy,
            'report_type' => $this->reportType,
            'export_format' => $this->exportFormat,
            'status' => $this->status,
            'invoice_status' => $this->invoiceStatus,
        ];
    }
}
