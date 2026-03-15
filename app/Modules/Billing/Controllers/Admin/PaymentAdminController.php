<?php

namespace App\Modules\Billing\Controllers\Admin;

use App\Models\Billing\Invoice;
use App\Models\Billing\Payment;
use App\Modules\Billing\Actions\DeletePaymentAction;
use App\Modules\Billing\Actions\RecordPaymentAction;
use App\Modules\Billing\Requests\RecordPaymentRequest;
use App\Modules\Billing\Resources\PaymentResource;
use App\Support\ApiResponse;
use Illuminate\Routing\Controller;

class PaymentAdminController extends Controller
{
    public function __construct(
        private RecordPaymentAction $recordPaymentAction,
        private DeletePaymentAction $deletePaymentAction,
    ) {}

    /**
     * List payments with filtering
     * GET /api/v1/admin/payments
     */
    public function index()
    {
        try {
            $query = Payment::with(['patient', 'invoice', 'receivedBy']);

            // Filter by invoice
            if ($invoiceId = request('invoice_id')) {
                $query->where('invoice_id', $invoiceId);
            }

            // Filter by patient
            if ($patientId = request('patient_id')) {
                $query->where('patient_id', $patientId);
            }

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

            $payments = $query->orderByDesc('payment_date')->paginate(15);

            return ApiResponse::success(
                PaymentResource::collection($payments),
                'Payments retrieved successfully',
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
     * Record payment(s) for an invoice
     * POST /api/v1/admin/invoices/{id}/payments
     */
    public function store($invoiceId, RecordPaymentRequest $request)
    {
        try {
            $invoice = Invoice::findOrFail($invoiceId);

            $payments = ($this->recordPaymentAction)(
                invoice: $invoice,
                payments: $request->payments,
                receivedBy: auth()->id()
            );

            return ApiResponse::success(
                PaymentResource::collection($payments),
                'Payments recorded successfully',
                201
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * Get payment details
     * GET /api/v1/admin/payments/{id}
     */
    public function show($id)
    {
        try {
            $payment = Payment::with(['patient', 'invoice', 'receivedBy'])
                ->findOrFail($id);

            return ApiResponse::success(
                new PaymentResource($payment),
                'Payment retrieved successfully'
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 404);
        }
    }

    /**
     * Delete payment (safe reversal)
     * DELETE /api/v1/admin/payments/{id}
     */
    public function destroy($id)
    {
        try {
            $payment = Payment::findOrFail($id);

            // Only admin can delete payments - already protected by middleware
            ($this->deletePaymentAction)($payment);

            return ApiResponse::success(
                null,
                'Payment deleted successfully'
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }
}

