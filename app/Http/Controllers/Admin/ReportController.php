<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserType;
use App\Http\Controllers\Concerns\AppliesSpecialtyScope;
use App\Http\Controllers\Controller;
use App\Models\Appointment\Appointment;
use App\Models\Billing\Invoice;
use App\Models\Clinic\MedicalSpecialty;
use App\Models\Clinic\Service;
use App\Models\Patient\Patient;
use App\Models\User;
use App\Modules\Reports\DTOs\ReportFilterDTO;
use App\Modules\Reports\Services\ReportExportService;
use App\Modules\Reports\Services\ReportRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    use AppliesSpecialtyScope;

    public function __construct(
        private readonly ReportRegistry $reportRegistry,
        private readonly ReportExportService $reportExportService,
    ) {
    }

    /**
     * Display the reports dashboard.
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $fromDate = $request->input('from_date', now()->startOfMonth()->format('Y-m-d'));
        $toDate = $request->input('to_date', now()->format('Y-m-d'));
        $serviceId = $request->input('service_id');
        $doctorId = $request->input('doctor_id');
        $specialtyId = $request->input('specialty_id');
        $status = $request->input('status');
        $canViewFinancial = $request->user()?->can('reports.financial') ?? false;

        if (! $this->isSpecialtyScopeBypassed()) {
            $userSpecialtyId = $this->currentSpecialtyId();
            $specialtyId = $userSpecialtyId ?: null;

            if (! $specialtyId) {
                // Non-admin users without a specialty assignment get empty report data.
                $specialtyId = -1;
            }
        }

        // Revenue Report Data (restricted)
        $revenueData = $canViewFinancial
            ? $this->getRevenueReport($fromDate, $toDate, $serviceId, $doctorId, $specialtyId, $status)
            : [
                'total_revenue' => 0,
                'paid_amount' => 0,
                'remaining_amount' => 0,
                'invoices_count' => 0,
                'monthly_revenue' => [],
            ];

        // Appointments Report Data
        $appointmentsData = $this->getAppointmentsReport($fromDate, $toDate, $serviceId, $doctorId, $specialtyId, $status);

        // Services Report Data
        $servicesData = $this->getServicesReport($fromDate, $toDate, $serviceId);

        // Patients Growth Report Data
        $patientsData = $this->getPatientsGrowthReport($fromDate, $toDate);

        // Get filter options
        $services = Service::query()->where('is_active', true)->orderBy('name_en')->get();

        $doctors = User::query()
            ->where('user_type', UserType::DOCTOR->value)
            ->where('status', 'active')
            ->when($specialtyId, fn ($query) => $query->where('specialty_id', $specialtyId))
            ->orderBy('full_name')
            ->get();

        $specialties = MedicalSpecialty::query()
            ->where('is_active', true)
            ->when(! $this->isSpecialtyScopeBypassed() && $this->currentSpecialtyId(), fn ($query) => $query->whereKey($this->currentSpecialtyId()))
            ->orderBy('name')
            ->get();

        // Top Services
        $topServices = $this->getTopServices($fromDate, $toDate, $specialtyId);

        // Daily Revenue
        $dailyRevenue = $this->getDailyRevenue($fromDate, $toDate, $specialtyId);

        // Specialty-aware data
        $appointmentsBySpecialty = $this->getAppointmentsBySpecialty($fromDate, $toDate, $status);
        $doctorsBySpecialty = $this->getDoctorsBySpecialty();
        $revenueBySpecialty = $canViewFinancial
            ? $this->getRevenueBySpecialty($fromDate, $toDate, $status)
            : collect([]);
        $dailyWorkloadBySpecialty = $this->getDailyWorkloadBySpecialty($fromDate, $toDate, $doctorId, $status);

        return view('admin.reports.index', compact(
            'revenueData',
            'appointmentsData',
            'servicesData',
            'patientsData',
            'services',
            'doctors',
            'specialties',
            'topServices',
            'dailyRevenue',
            'appointmentsBySpecialty',
            'doctorsBySpecialty',
            'revenueBySpecialty',
            'dailyWorkloadBySpecialty',
            'fromDate',
            'toDate',
            'serviceId',
            'doctorId',
            'specialtyId',
            'status',
            'canViewFinancial'
        ));
    }

    /**
     * Get revenue report data.
     */
    private function getRevenueReport($fromDate, $toDate, $serviceId = null, $doctorId = null, $specialtyId = null, $status = null)
    {
        try {
            $query = Invoice::query()->whereBetween('issued_at', [$fromDate, $toDate]);

            if ($status) {
                $query->where('status', $status);
            }

            if ($serviceId || $doctorId || $specialtyId) {
                $query->whereHas('items', function ($q) use ($serviceId) {
                    if ($serviceId) {
                        $q->where('service_id', $serviceId);
                    }
                });
            }

            if ($doctorId) {
                $query->whereHas('visit', function ($q) use ($doctorId) {
                    $q->where('doctor_id', $doctorId);
                });
            }

            if ($specialtyId) {
                $query->whereHas('items.service.category', function ($q) use ($specialtyId) {
                    $q->where('medical_specialty_id', $specialtyId);
                });
            }

            $totalRevenue = $query->sum('total');
            $paidAmount = $query->sum('paid_amount');
            $remainingAmount = $query->sum('remaining_amount');
            $invoicesCount = $query->count();

            // Monthly revenue for chart
            $monthlyRevenue = Invoice::query()
                ->selectRaw('MONTH(issued_at) as month, SUM(total) as total')
                ->whereBetween('issued_at', [$fromDate, $toDate])
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('total', 'month')
                ->toArray();

            return [
                'total_revenue' => $totalRevenue,
                'paid_amount' => $paidAmount,
                'remaining_amount' => $remainingAmount,
                'invoices_count' => $invoicesCount,
                'monthly_revenue' => $monthlyRevenue,
            ];
        } catch (\Exception $e) {
            return [
                'total_revenue' => 0,
                'paid_amount' => 0,
                'remaining_amount' => 0,
                'invoices_count' => 0,
                'monthly_revenue' => [],
            ];
        }
    }

    /**
     * Get appointments report data.
     */
    private function getAppointmentsReport($fromDate, $toDate, $serviceId = null, $doctorId = null, $specialtyId = null, $status = null)
    {
        try {
            $query = Appointment::query()->whereBetween('appointment_date', [$fromDate, $toDate]);

            if ($serviceId) {
                $query->where('service_id', $serviceId);
            }

            if ($doctorId) {
                $query->where('assigned_doctor_id', $doctorId);
            }

            if ($specialtyId) {
                $query->where('specialty_id', $specialtyId);
            }

            if ($status) {
                $query->where('status', $status);
            }

            $totalAppointments = $query->count();
            $completedAppointments = (clone $query)->where('status', 'completed')->count();
            $cancelledAppointments = (clone $query)
                ->whereIn('status', ['cancelled_by_clinic', 'cancelled_by_patient'])
                ->count();
            $pendingAppointments = (clone $query)->where('status', 'pending')->count();

            // Appointments by status for chart
            $appointmentsByStatus = Appointment::query()
                ->selectRaw('status, COUNT(*) as count')
                ->whereBetween('appointment_date', [$fromDate, $toDate])
                ->when($specialtyId, fn ($q) => $q->where('specialty_id', $specialtyId))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            return [
                'total_appointments' => $totalAppointments,
                'completed_appointments' => $completedAppointments,
                'cancelled_appointments' => $cancelledAppointments,
                'pending_appointments' => $pendingAppointments,
                'appointments_by_status' => $appointmentsByStatus,
            ];
        } catch (\Exception $e) {
            return [
                'total_appointments' => 0,
                'completed_appointments' => 0,
                'cancelled_appointments' => 0,
                'pending_appointments' => 0,
                'appointments_by_status' => [],
            ];
        }
    }

    /**
     * Get services report data.
     */
    private function getServicesReport($fromDate, $toDate, $serviceId = null)
    {
        try {
            $query = DB::table('invoice_items')
                ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
                ->whereBetween('invoices.issued_at', [$fromDate, $toDate]);

            if ($serviceId) {
                $query->where('invoice_items.service_id', $serviceId);
            }

            $totalServices = $query->sum('invoice_items.quantity');
            $totalServiceRevenue = $query->sum('invoice_items.total');

            // Services revenue distribution for chart
            $servicesRevenue = DB::table('invoice_items')
                ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
                ->join('services', 'invoice_items.service_id', '=', 'services.id')
                ->selectRaw('services.name_en, SUM(invoice_items.total) as revenue')
                ->whereBetween('invoices.issued_at', [$fromDate, $toDate])
                ->groupBy('services.id', 'services.name_en')
                ->orderByDesc('revenue')
                ->limit(10)
                ->pluck('revenue', 'name_en')
                ->toArray();

            return [
                'total_services' => $totalServices,
                'total_service_revenue' => $totalServiceRevenue,
                'services_revenue' => $servicesRevenue,
            ];
        } catch (\Exception $e) {
            return [
                'total_services' => 0,
                'total_service_revenue' => 0,
                'services_revenue' => [],
            ];
        }
    }

    /**
     * Get patients growth report data.
     */
    private function getPatientsGrowthReport($fromDate, $toDate)
    {
        try {
            $query = Patient::query()->whereBetween('created_at', [$fromDate, $toDate]);

            $newPatients = $query->count();
            $totalPatients = Patient::query()->count();
            $activePatients = Patient::query()->where('status', 'active')->count();

            // Monthly patient growth for chart
            $monthlyGrowth = Patient::query()
                ->selectRaw('MONTH(created_at) as month, COUNT(*) as count')
                ->whereBetween('created_at', [$fromDate, $toDate])
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('count', 'month')
                ->toArray();

            return [
                'new_patients' => $newPatients,
                'total_patients' => $totalPatients,
                'active_patients' => $activePatients,
                'monthly_growth' => $monthlyGrowth,
            ];
        } catch (\Exception $e) {
            return [
                'new_patients' => 0,
                'total_patients' => 0,
                'active_patients' => 0,
                'monthly_growth' => [],
            ];
        }
    }

    /**
     * Get top services by revenue.
     */
    private function getTopServices($fromDate, $toDate, $specialtyId = null)
    {
        try {
            return DB::table('invoice_items')
                ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
                ->join('services', 'invoice_items.service_id', '=', 'services.id')
                ->leftJoin('service_categories', 'services.category_id', '=', 'service_categories.id')
                ->selectRaw('services.name_en as service_name, services.name_ar as service_name_ar, SUM(invoice_items.quantity) as total_quantity, SUM(invoice_items.total) as total_revenue')
                ->whereBetween('invoices.issued_at', [$fromDate, $toDate])
                ->when($specialtyId, fn ($query) => $query->where('service_categories.medical_specialty_id', $specialtyId))
                ->groupBy('services.id', 'services.name_en', 'services.name_ar')
                ->orderByDesc('total_revenue')
                ->limit(10)
                ->get();
        } catch (\Exception $e) {
            return collect([]);
        }
    }

    /**
     * Get daily revenue for the date range.
     */
    private function getDailyRevenue($fromDate, $toDate, $specialtyId = null)
    {
        try {
            return DB::table('invoice_items')
                ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
                ->leftJoin('services', 'invoice_items.service_id', '=', 'services.id')
                ->leftJoin('service_categories', 'services.category_id', '=', 'service_categories.id')
                ->selectRaw('DATE(invoices.issued_at) as date, SUM(invoice_items.total) as revenue, COUNT(DISTINCT invoices.id) as invoices_count')
                ->whereBetween('invoices.issued_at', [$fromDate, $toDate])
                ->when($specialtyId, fn ($query) => $query->where('service_categories.medical_specialty_id', $specialtyId))
                ->groupBy('date')
                ->orderBy('date')
                ->get();
        } catch (\Exception $e) {
            return collect([]);
        }
    }

    /**
     * Get appointments by specialty for the report.
     */
    private function getAppointmentsBySpecialty($fromDate, $toDate, $status = null)
    {
        try {
            return DB::table('appointments')
                ->leftJoin('medical_specialties', 'appointments.specialty_id', '=', 'medical_specialties.id')
                ->selectRaw("COALESCE(medical_specialties.name, 'Unassigned') as specialty_name, COUNT(*) as appointments_count")
                ->whereBetween('appointments.appointment_date', [$fromDate, $toDate])
                ->when($status, fn ($q) => $q->where('appointments.status', $status))
                ->groupBy('medical_specialties.id', 'medical_specialties.name')
                ->orderByDesc('appointments_count')
                ->get();
        } catch (\Exception $e) {
            return collect([]);
        }
    }

    /**
     * Get doctors by specialty for the report.
     */
    private function getDoctorsBySpecialty()
    {
        try {
            return DB::table('medical_specialties')
                ->leftJoin('users', function ($join) {
                    $join->on('users.specialty_id', '=', 'medical_specialties.id')
                        ->where('users.user_type', UserType::DOCTOR->value)
                        ->where('users.status', 'active');
                })
                ->selectRaw('medical_specialties.name as specialty_name, COUNT(users.id) as doctors_count')
                ->groupBy('medical_specialties.id', 'medical_specialties.name')
                ->orderBy('medical_specialties.name')
                ->get();
        } catch (\Exception $e) {
            return collect([]);
        }
    }

    /**
     * Get revenue by specialty for the report.
     */
    private function getRevenueBySpecialty($fromDate, $toDate, $status = null)
    {
        try {
            return DB::table('invoice_items')
                ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
                ->leftJoin('services', 'invoice_items.service_id', '=', 'services.id')
                ->leftJoin('service_categories', 'services.category_id', '=', 'service_categories.id')
                ->leftJoin('medical_specialties', 'service_categories.medical_specialty_id', '=', 'medical_specialties.id')
                ->selectRaw("COALESCE(medical_specialties.name, 'Unassigned') as specialty_name, SUM(invoice_items.total) as revenue_total")
                ->whereBetween('invoices.issued_at', [$fromDate, $toDate])
                ->when($status, fn ($q) => $q->where('invoices.status', $status))
                ->groupBy('medical_specialties.id', 'medical_specialties.name')
                ->orderByDesc('revenue_total')
                ->get();
        } catch (\Exception $e) {
            return collect([]);
        }
    }

    /**
     * Get daily workload by specialty for the report.
     */
    private function getDailyWorkloadBySpecialty($fromDate, $toDate, $doctorId = null, $status = null)
    {
        try {
            return DB::table('appointments')
                ->leftJoin('medical_specialties', 'appointments.specialty_id', '=', 'medical_specialties.id')
                ->leftJoin('services', 'appointments.service_id', '=', 'services.id')
                ->selectRaw("appointments.appointment_date as workload_date, COALESCE(medical_specialties.name, 'Unassigned') as specialty_name, COUNT(appointments.id) as appointments_count, COALESCE(SUM(services.duration_minutes), 0) as total_minutes")
                ->whereBetween('appointments.appointment_date', [$fromDate, $toDate])
                ->when($doctorId, fn ($q) => $q->where('appointments.assigned_doctor_id', $doctorId))
                ->when($status, fn ($q) => $q->where('appointments.status', $status))
                ->groupBy('appointments.appointment_date', 'medical_specialties.id', 'medical_specialties.name')
                ->orderBy('appointments.appointment_date')
                ->orderBy('specialty_name')
                ->get();
        } catch (\Exception $e) {
            return collect([]);
        }
    }

    /**
     * Export report to PDF using the shared reports module.
     */
    public function exportPdf(Request $request)
    {
        $reportType = $request->input('report_type', 'revenue');

        if ($this->isFinancialReportType($reportType) && !($request->user()?->can('reports.financial') ?? false)) {
            abort(403, 'Unauthorized to export financial reports.');
        }

        return $this->reportExportService->exportPdf($reportType, $this->toFilterDto($request, $reportType));
    }

    /**
     * Export report to Excel using the shared reports module.
     */
    public function exportExcel(Request $request)
    {
        $reportType = $request->input('report_type', 'revenue');

        if ($this->isFinancialReportType($reportType) && !($request->user()?->can('reports.financial') ?? false)) {
            abort(403, 'Unauthorized to export financial reports.');
        }

        return $this->reportExportService->exportExcel($reportType, $this->toFilterDto($request, $reportType));
    }

    /**
     * Print report (returns print view).
     */
    public function print(Request $request)
    {
        $reportType = $request->input('report_type');

        if ($reportType) {
            if ($this->isFinancialReportType($reportType) && !($request->user()?->can('reports.financial') ?? false)) {
                abort(403, 'Unauthorized to print financial reports.');
            }

            $report = $this->reportRegistry->generate($reportType, $this->toFilterDto($request, $reportType));

            return view('admin.reports.print', ['report' => $report, 'reportType' => $reportType]);
        }

        return $this->index($request);
    }

    private function toFilterDto(Request $request, string $reportType): ReportFilterDTO
    {
        return ReportFilterDTO::fromArray([
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date'),
            'doctor_id' => $request->filled('doctor_id') ? (int) $request->input('doctor_id') : null,
            'service_id' => $request->filled('service_id') ? (int) $request->input('service_id') : null,
            'specialty_id' => $request->filled('specialty_id') ? (int) $request->input('specialty_id') : null,
            'group_by' => $request->input('group_by'),
            'report_type' => $reportType,
            'export_format' => $request->input('export_format'),
            'status' => $request->input('status'),
            'invoice_status' => $request->input('invoice_status'),
        ]);
    }

    private function isFinancialReportType(string $reportType): bool
    {
        return in_array($reportType, ['revenue', 'invoices'], true);
    }
}
