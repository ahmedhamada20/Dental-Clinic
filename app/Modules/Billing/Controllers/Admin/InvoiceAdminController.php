<?php

namespace App\Modules\Billing\Controllers\Admin;

use App\Models\Billing\Invoice;
use App\Models\Visit\Visit;
use App\Modules\Billing\Actions\AddInvoiceItemAction;
use App\Modules\Billing\Actions\CancelInvoiceAction;
use App\Modules\Billing\Actions\CreateInvoiceFromVisitAction;
use App\Modules\Billing\Actions\DeleteInvoiceItemAction;
use App\Modules\Billing\Actions\FinalizeInvoiceAction;
use App\Modules\Billing\Actions\UpdateInvoiceAction;
use App\Modules\Billing\DTOs\CreateInvoiceItemData;
use App\Modules\Billing\DTOs\UpdateInvoiceData;
use App\Modules\Billing\Requests\AddInvoiceItemRequest;
use App\Modules\Billing\Requests\CancelInvoiceRequest;
use App\Modules\Billing\Requests\DeleteInvoiceItemRequest;
use App\Modules\Billing\Requests\FinalizeInvoiceRequest;
use App\Modules\Billing\Requests\StoreInvoiceRequest;
use App\Modules\Billing\Requests\UpdateInvoiceRequest;
use App\Modules\Billing\Resources\InvoiceDetailsResource;
use App\Modules\Billing\Resources\InvoiceResource;
use App\Modules\Billing\Services\PdfInvoiceService;
use App\Support\ApiResponse;
use Illuminate\Routing\Controller;

class InvoiceAdminController extends Controller
{
    public function __construct(
        private CreateInvoiceFromVisitAction $createInvoiceAction,
        private UpdateInvoiceAction $updateInvoiceAction,
        private AddInvoiceItemAction $addItemAction,
        private DeleteInvoiceItemAction $deleteItemAction,
        private FinalizeInvoiceAction $finalizeAction,
        private CancelInvoiceAction $cancelAction,
        private PdfInvoiceService $pdfService,
    ) {}

    /**
     * List invoices with filtering
     * GET /api/v1/admin/invoices
     */
    public function index()
    {
        try {
            $query = Invoice::with(['patient', 'items', 'payments', 'promotion', 'createdBy']);

            // Filter by status
            if ($status = request('status')) {
                $query->where('status', $status);
            }

            // Filter by patient
            if ($patientId = request('patient_id')) {
                $query->where('patient_id', $patientId);
            }

            // Filter by date range
            if ($dateFrom = request('date_from')) {
                $query->whereDate('issued_at', '>=', $dateFrom);
            }
            if ($dateTo = request('date_to')) {
                $query->whereDate('issued_at', '<=', $dateTo);
            }

            // Filter by payment status
            if ($paymentStatus = request('payment_status')) {
                if ($paymentStatus === 'paid') {
                    $query->paid();
                } elseif ($paymentStatus === 'unpaid') {
                    $query->unpaid();
                }
            }

            $invoices = $query->orderByDesc('created_at')->paginate(15);

            return ApiResponse::success(
                InvoiceResource::collection($invoices),
                'Invoices retrieved successfully',
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
     * Create invoice from visit
     * POST /api/v1/admin/invoices
     */
    public function store(StoreInvoiceRequest $request)
    {
        try {
            $invoice = ($this->createInvoiceAction)(
                patientId: $request->patient_id,
                createdBy: auth()->id(),
                visitId: $request->visit_id,
                promotionId: $request->promotion_id,
                notes: $request->notes,
            );

            return ApiResponse::success(
                new InvoiceDetailsResource($invoice),
                'Invoice created successfully',
                201
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * Get invoice details
     * GET /api/v1/admin/invoices/{id}
     */
    public function show($id)
    {
        try {
            $invoice = Invoice::with(['patient', 'items', 'payments', 'promotion', 'createdBy'])
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
     * Update invoice
     * PUT /api/v1/admin/invoices/{id}
     */
    public function update($id, UpdateInvoiceRequest $request)
    {
        try {
            $invoice = Invoice::findOrFail($id);

            $data = UpdateInvoiceData::fromArray($request->validated());
            $updated = ($this->updateInvoiceAction)($invoice, $data);

            return ApiResponse::success(
                new InvoiceDetailsResource($updated),
                'Invoice updated successfully'
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * Add item to invoice
     * POST /api/v1/admin/invoices/{id}/items
     */
    public function addItem($id, AddInvoiceItemRequest $request)
    {
        try {
            $invoice = Invoice::findOrFail($id);

            $data = CreateInvoiceItemData::fromArray([
                ...$request->validated(),
                'invoice_id' => $invoice->id,
            ]);

            ($this->addItemAction)($invoice, $data);

            return ApiResponse::success(
                new InvoiceDetailsResource($invoice->fresh()),
                'Item added to invoice successfully',
                201
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * Delete invoice item
     * DELETE /api/v1/admin/invoice-items/{id}
     */
    public function deleteItem($id, DeleteInvoiceItemRequest $request)
    {
        try {
            $item = \App\Models\Billing\InvoiceItem::findOrFail($id);

            ($this->deleteItemAction)($item);

            return ApiResponse::success(
                null,
                'Item deleted successfully'
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * Finalize invoice
     * POST /api/v1/admin/invoices/{id}/finalize
     */
    public function finalize($id, FinalizeInvoiceRequest $request)
    {
        try {
            $invoice = Invoice::findOrFail($id);

            $finalized = ($this->finalizeAction)($invoice);

            return ApiResponse::success(
                new InvoiceDetailsResource($finalized),
                'Invoice finalized successfully'
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * Cancel invoice
     * POST /api/v1/admin/invoices/{id}/cancel
     */
    public function cancel($id, CancelInvoiceRequest $request)
    {
        try {
            $invoice = Invoice::findOrFail($id);

            $cancelled = ($this->cancelAction)($invoice, $request->reason);

            return ApiResponse::success(
                new InvoiceDetailsResource($cancelled),
                'Invoice cancelled successfully'
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * Get invoice PDF
     * GET /api/v1/admin/invoices/{id}/pdf
     */
    public function pdf($id)
    {
        try {
            $invoice = Invoice::with(['patient', 'items', 'payments', 'promotion', 'createdBy'])
                ->findOrFail($id);

            if (!$this->pdfService->canGenerate($invoice)) {
                return ApiResponse::error('Invoice must be finalized to generate PDF', 400);
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

