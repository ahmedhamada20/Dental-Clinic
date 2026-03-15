<?php

namespace App\Modules\Billing\Controllers\Admin;

use App\Models\Billing\Promotion;
use App\Modules\Billing\Actions\CreatePromotionAction;
use App\Modules\Billing\Actions\UpdatePromotionAction;
use App\Modules\Billing\DTOs\CreatePromotionData;
use App\Modules\Billing\DTOs\UpdatePromotionData;
use App\Modules\Billing\Requests\StorePromotionRequest;
use App\Modules\Billing\Requests\UpdatePromotionRequest;
use App\Modules\Billing\Resources\PromotionResource;
use App\Support\ApiResponse;
use Illuminate\Routing\Controller;

class PromotionAdminController extends Controller
{
    public function __construct(
        private CreatePromotionAction $createAction,
        private UpdatePromotionAction $updateAction,
    ) {}

    /**
     * List promotions with filtering
     * GET /api/v1/admin/promotions
     */
    public function index()
    {
        try {
            $query = Promotion::query();

            // Filter by status
            if ($isActive = request('is_active')) {
                $query->where('is_active', filter_var($isActive, FILTER_VALIDATE_BOOLEAN));
            }

            // Filter by type
            if ($type = request('type')) {
                $query->where('promotion_type', $type);
            }

            // Filter active now
            if (request('active_now') === 'true') {
                $query->activeNow();
            }

            // Search by code
            if ($code = request('code')) {
                $query->where('code', 'like', "%{$code}%");
            }

            $promotions = $query->orderByDesc('created_at')->paginate(15);

            return ApiResponse::success(
                PromotionResource::collection($promotions),
                'Promotions retrieved successfully',
                extra: ['pagination' => [
                    'total' => $promotions->total(),
                    'count' => $promotions->count(),
                    'per_page' => $promotions->perPage(),
                    'current_page' => $promotions->currentPage(),
                    'last_page' => $promotions->lastPage(),
                ]]
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * Create promotion
     * POST /api/v1/admin/promotions
     */
    public function store(StorePromotionRequest $request)
    {
        try {
            $data = CreatePromotionData::fromArray($request->validated());
            $promotion = ($this->createAction)($data);

            return ApiResponse::success(
                new PromotionResource($promotion),
                'Promotion created successfully',
                201
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * Get promotion details
     * GET /api/v1/admin/promotions/{id}
     */
    public function show($id)
    {
        try {
            $promotion = Promotion::findOrFail($id);

            return ApiResponse::success(
                new PromotionResource($promotion),
                'Promotion retrieved successfully'
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 404);
        }
    }

    /**
     * Update promotion
     * PUT /api/v1/admin/promotions/{id}
     */
    public function update($id, UpdatePromotionRequest $request)
    {
        try {
            $promotion = Promotion::findOrFail($id);

            $data = UpdatePromotionData::fromArray($request->validated());
            $updated = ($this->updateAction)($promotion, $data);

            return ApiResponse::success(
                new PromotionResource($updated),
                'Promotion updated successfully'
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * Toggle promotion status
     * POST /api/v1/admin/promotions/{id}/toggle-status
     */
    public function toggleStatus($id)
    {
        try {
            $promotion = Promotion::findOrFail($id);
            $promotion->update(['is_active' => !$promotion->is_active]);

            return ApiResponse::success(
                new PromotionResource($promotion),
                'Promotion status toggled successfully'
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * Delete promotion
     * DELETE /api/v1/admin/promotions/{id}
     */
    public function destroy($id)
    {
        try {
            $promotion = Promotion::findOrFail($id);

            // Check if promotion is being used in invoices
            if ($promotion->invoices()->exists()) {
                return ApiResponse::error('Cannot delete promotion that is being used in invoices', 400);
            }

            $promotion->delete();

            return ApiResponse::success(
                null,
                'Promotion deleted successfully'
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }
}

