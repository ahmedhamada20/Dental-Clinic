<?php

namespace App\Modules\Billing\Controllers\Patient;

use App\Models\Billing\Invoice;
use App\Modules\Billing\Resources\InvoiceDetailsResource;
use App\Modules\Billing\Resources\InvoiceResource;
use App\Modules\Billing\Services\PdfInvoiceService;
use App\Support\ApiResponse;
use Illuminate\Routing\Controller;

class PatientInvoiceController extends Controller
{
    public function __construct(
        private PdfInvoiceService $pdfService,
    ) {}

    /**
     * List patient's invoices
     * GET /api/v1/patient/invoices
     */
    public function index()
    {
        try {
            $patientId = auth()->user()->id;

            $query = Invoice::where('patient_id', $patientId)
                ->with(['items', 'payments', 'promotion', 'createdBy']);

            // Filter by status
            if ($status = request('status')) {
                $query->where('status', $status);
            }

            // Filter by payment status
            if ($paymentStatus = request('payment_status')) {
                if ($paymentStatus === 'paid') {
                    $query->paid();
                } elseif ($paymentStatus === 'unpaid') {
                    $query->unpaid();
                }
            }

            // Filter by date range
            if ($dateFrom = request('date_from')) {
                $query->whereDate('issued_at', '>=', $dateFrom);
            }
            if ($dateTo = request('date_to')) {
                $query->whereDate('issued_at', '<=', $dateTo);
            }

            $invoices = $query->orderByDesc('created_at')->paginate(10);

            return ApiResponse::success(
                InvoiceResource::collection($invoices),
                'Your invoices retrieved successfully',
                extra: ['pagination' => [
                    'total' => $invoices->total(),
                    'count' => $invoices->count(),
                    'per_page' => $invoices->perPage(),
                    'current_page' => $invoices->currentPage(),
                    'last_page' => $invoices->lastPage(),
                ]]
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * Get invoice details
     * GET /api/v1/patient/invoices/{id}
     */
    public function show($id)
    {
        try {
            $invoice = Invoice::where('patient_id', auth()->user()->id)
                ->with(['patient', 'items', 'payments', 'promotion', 'createdBy'])
                ->findOrFail($id);

            return ApiResponse::success(
                new InvoiceDetailsResource($invoice),
                'Invoice retrieved successfully'
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 404);
        }
    }

    /**
     * Download invoice PDF
     * GET /api/v1/patient/invoices/{id}/pdf
     */
    public function download($id)
    {
        try {
            $invoice = Invoice::where('patient_id', auth()->user()->id)
                ->with(['patient', 'items', 'payments', 'promotion', 'createdBy'])
                ->findOrFail($id);

            if (!$this->pdfService->canGenerate($invoice)) {
                return ApiResponse::error('Invoice must be finalized to download PDF', 400);
            }

            // TODO: Implement actual PDF generation and download
            return ApiResponse::success(
                [
                    'invoice_no' => $invoice->invoice_no,
                    'status' => 'pdf_generation_not_implemented',
                    'message' => 'PDF generation service skeleton - implement with MPDF/Dompdf',
                ],
                'PDF service endpoint ready for implementation'
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 404);
        }
    }
}

