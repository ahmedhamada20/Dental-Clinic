<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AppointmentStatus;
use App\Enums\BookingSource;
use App\Enums\WaitingListStatus;
use App\Models\Appointment\Appointment;
use App\Http\Controllers\Controller;
use App\Models\Appointment\WaitingListRequest;
use App\Models\Clinic\MedicalSpecialty;
use App\Models\Clinic\Service;
use App\Models\Patient\Patient;
use App\Models\System\SystemNotification;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WaitingListController extends Controller
{
    /**
     * Display a listing of waiting list requests.
     */
    public function index(Request $request): View
    {
        $query = WaitingListRequest::query()
            ->with(['patient', 'service']);

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('preferred_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('preferred_date', '<=', $request->date_to);
        }

        if ($request->filled('specialty_id')) {
            $query->whereHas('service.category', function ($serviceCategoryQuery) use ($request) {
                $serviceCategoryQuery->where('medical_specialty_id', $request->integer('specialty_id'));
            });
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('patient', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Default ordering - pending first, then by preferred date
        $waitingListRequests = $query
            ->orderByRaw("CASE
                WHEN status = 'waiting' THEN 1
                WHEN status = 'notified' THEN 2
                WHEN status = 'booked' THEN 3
                WHEN status = 'cancelled' THEN 4
                WHEN status = 'expired' THEN 5
                ELSE 5 END")
            ->orderBy('preferred_date', 'asc')
            ->paginate(15)
            ->withQueryString();

        $statuses = WaitingListStatus::cases();

        $stats = [
            'total_waiting' => WaitingListRequest::query()->count(),
            'pending' => WaitingListRequest::query()->where('status', WaitingListStatus::PENDING->value)->count(),
            'converted' => WaitingListRequest::query()->where('status', WaitingListStatus::FULFILLED->value)->count(),
            'cancelled' => WaitingListRequest::query()->where('status', WaitingListStatus::CANCELLED->value)->count(),
        ];

        $patients = Patient::query()->orderBy('full_name')->get(['id', 'full_name', 'phone']);
        $specialties = MedicalSpecialty::query()->where('is_active', true)->orderBy('name')->get();

        return view('admin.waiting-list.index', compact('waitingListRequests', 'statuses', 'stats', 'patients', 'specialties'));
    }

    public function create(): View
    {
        return view('admin.waiting-list.create', [
            'patients' => Patient::query()->orderBy('full_name')->get(['id', 'full_name', 'phone']),
            'specialties' => MedicalSpecialty::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function show(WaitingListRequest $waitingListRequest): View
    {
        $waitingListRequest->loadMissing(['patient', 'service.category']);

        $queuePosition = WaitingListRequest::query()
            ->where('status', WaitingListStatus::PENDING->value)
            ->where('created_at', '<=', $waitingListRequest->created_at)
            ->count();

        $queueTotal = WaitingListRequest::query()
            ->where('status', WaitingListStatus::PENDING->value)
            ->count();

        return view('admin.waiting-list.show', compact('waitingListRequest', 'queuePosition', 'queueTotal'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'service_id' => ['nullable', 'integer', 'exists:services,id', 'required_without:specialty_id'],
            'specialty_id' => ['nullable', 'integer', 'exists:medical_specialties,id', 'required_without:service_id'],
            'preferred_date' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        $serviceId = $validated['service_id'] ?? null;

        if (! $serviceId && ! empty($validated['specialty_id'])) {
            $serviceId = Service::query()
                ->whereHas('category', fn ($query) => $query->where('medical_specialty_id', $validated['specialty_id']))
                ->where('is_active', true)
                ->value('id');
        }

        if (! $serviceId) {
            return redirect()
                ->back()
                ->withErrors(['service_id' => 'No active service is available for the selected specialty.'])
                ->withInput();
        }

        WaitingListRequest::query()->create([
            'patient_id' => $validated['patient_id'],
            'service_id' => $serviceId,
            'preferred_date' => $validated['preferred_date'] ?? null,
            'status' => WaitingListStatus::PENDING,
        ]);

        return redirect()
            ->route('admin.waiting-list.index')
            ->with('success', 'Waiting list request created successfully.');
    }

    /**
     * Mark a waiting list request as notified.
     */
    public function notify(WaitingListRequest $waitingListRequest): RedirectResponse
    {
        if ($waitingListRequest->status !== WaitingListStatus::PENDING) {
            return redirect()
                ->back()
                ->with('error', 'Only pending requests can be marked as notified.');
        }


        $waitingListRequest->update([
            'status' => WaitingListStatus::NOTIFIED,
            'notified_at' => now(),
        ]);

        SystemNotification::create([
            'notifiable_type' => Patient::class,
            'notifiable_id' => $waitingListRequest->patient_id,
            'channel' => "in_app",
            'body' => "Patient has been marked as notified successfully",
            'type' => "appointment_confirmed",
            'title' => "NOTIFIED",
        ]);

        return redirect()
            ->back()
            ->with('success', 'Patient has been marked as notified successfully.');
    }

    /**
     * Convert a waiting list request to an appointment.
     */
    public function convert(WaitingListRequest $waitingListRequest): RedirectResponse
    {
        if ($waitingListRequest->status->isFinalized()) {
            return redirect()
                ->back()
                ->with('error', 'This request has already been finalized.');
        }

        $service = $waitingListRequest->service()->with('category')->first();
        if (! $service) {
            return redirect()
                ->back()
                ->with('error', 'Unable to convert request because the linked service no longer exists.');
        }

        $appointmentDate = $waitingListRequest->preferred_date?->toDateString() ?? now()->toDateString();
        $startTime = $waitingListRequest->preferred_from_time
            ?? Carbon::now()->addHour()->minute(0)->second(0)->format('H:i:s');
        $duration = max(1, (int) ($service->duration_minutes ?? 30));
        $endTime = Carbon::createFromFormat('H:i:s', $startTime)->addMinutes($duration)->format('H:i:s');

        DB::transaction(function () use ($waitingListRequest, $service, $appointmentDate, $startTime, $endTime) {
            $appointment = Appointment::query()->create([
                'appointment_no' => 'APT-' . now()->format('YmdHis') . '-' . random_int(1000, 9999),
                'patient_id' => $waitingListRequest->patient_id,
                'service_id' => $service->id,
                'specialty_id' => $service->category?->medical_specialty_id,
                'appointment_date' => $appointmentDate,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'status' => AppointmentStatus::PENDING,
                'booking_source' => BookingSource::WEB_APP,
                'notes' => 'Created from waiting list request #' . $waitingListRequest->id,
            ]);

            $waitingListRequest->update([
                'status' => WaitingListStatus::FULFILLED,
                'booked_appointment_id' => $appointment->id,
                'expires_at' => null,
            ]);
        });

        return redirect()
            ->back()
            ->with('success', 'Request converted successfully and appointment created.');
    }

    /**
     * Remove the specified waiting list request.
     */
    public function destroy(WaitingListRequest $waitingListRequest): RedirectResponse
    {
        $patientName = $waitingListRequest->patient->full_name ?? 'Unknown';

        $waitingListRequest->delete();

        return redirect()
            ->back()
            ->with('success', "Waiting list request for {$patientName} has been deleted successfully.");
    }

    /**
     * Cancel a waiting list request.
     */
    public function cancel(WaitingListRequest $waitingListRequest): RedirectResponse
    {
        if ($waitingListRequest->status->isFinalized()) {
            return redirect()
                ->back()
                ->with('error', 'This request has already been finalized.');
        }

        $waitingListRequest->update([
            'status' => WaitingListStatus::CANCELLED,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Waiting list request has been cancelled successfully.');
    }
}

