<?php

namespace App\Modules\Billing\Controllers\Patient;

use App\Models\Billing\Payment;
use App\Modules\Billing\Resources\PaymentResource;
use App\Support\ApiResponse;
use Illuminate\Routing\Controller;

class PatientPaymentController extends Controller
{
    /**
     * List patient's payments
     * GET /api/v1/patient/payments
     */
    public function index()
    {
        try {
            $patientId = auth()->user()->id;

            $query = Payment::where('patient_id', $patientId)
                ->with(['invoice', 'receivedBy']);

            // Filter by payment method
            if ($method = request('payment_method')) {
                $query->where('payment_method', $method);
            }

            // Filter by date range
            if ($dateFrom = request('date_from')) {
                $query->whereDate('payment_date', '>=', $dateFrom);
            }
            if ($dateTo = request('date_to')) {
                $query->whereDate('payment_date', '<=', $dateTo);
            }

            $payments = $query->orderByDesc('payment_date')->paginate(10);

            return ApiResponse::success(
                PaymentResource::collection($payments),
                'Your payments retrieved successfully',
                extra: ['pagination' => [
                    'total' => $payments->total(),
                    'count' => $payments->count(),
                    'per_page' => $payments->perPage(),
                    'current_page' => $payments->currentPage(),
                    'last_page' => $payments->lastPage(),
                ]]
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * Get payment details
     * GET /api/v1/patient/payments/{id}
     */
    public function show($id)
    {
        try {
            $payment = Payment::where('patient_id', auth()->user()->id)
                ->with(['invoice', 'receivedBy'])
                ->findOrFail($id);

            return ApiResponse::success(
                new PaymentResource($payment),
                'Payment retrieved successfully'
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 404);
        }
    }
}

