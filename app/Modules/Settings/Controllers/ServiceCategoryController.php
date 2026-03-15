<?php

namespace App\Modules\Settings\Controllers;

use App\Models\Clinic\ServiceCategory;
use App\Modules\Settings\Requests\StoreServiceCategoryRequest;
use App\Modules\Settings\Requests\UpdateServiceCategoryRequest;
use App\Modules\Settings\Resources\ServiceCategoryResource;
use App\Support\ApiResponse;
use Illuminate\Routing\Controller;

class ServiceCategoryController extends Controller
{
    /**
     * List all service categories.
     * GET /api/v1/admin/service-categories
     */
    public function index(): mixed
    {
        try {
            $search = request()->query('search');
            $perPage = request()->query('per_page', 15);
            $isActive = request()->query('is_active');
            $medicalSpecialtyId = request()->query('medical_specialty_id');

            $query = ServiceCategory::query()->with('medicalSpecialty');

            // Search by name
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name_en', 'like', "%{$search}%")
                        ->orWhere('name_ar', 'like', "%{$search}%");
                });
            }

            // Filter by specialty
            if ($medicalSpecialtyId) {
                $query->where('medical_specialty_id', $medicalSpecialtyId);
            }

            // Filter by active status
            if ($isActive !== null) {
                $query->where('is_active', filter_var($isActive, FILTER_VALIDATE_BOOLEAN));
            }

            $categories = $query
                ->withCount('services')
                ->orderBy('sort_order')
                ->orderBy('name_en')
                ->paginate($perPage);

            return ApiResponse::paginated(
                $categories,
                'Service categories retrieved successfully',
                200
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Failed to retrieve categories: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Create a new service category.
     * POST /api/v1/admin/service-categories
     */
    public function store(StoreServiceCategoryRequest $request): mixed
    {
        try {
            $category = ServiceCategory::create([
                'medical_specialty_id' => $request->integer('medical_specialty_id'),
                'name_ar' => $request->input('name_ar'),
                'name_en' => $request->input('name_en'),
                'sort_order' => $request->input('sort_order', 0),
                'is_active' => $request->boolean('is_active', true),
            ]);

            $category->load('medicalSpecialty');

            return ApiResponse::success(
                new ServiceCategoryResource($category),
                'Service category created successfully',
                201
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Failed to create category: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Get service category details.
     * GET /api/v1/admin/service-categories/{id}
     */
    public function show(ServiceCategory $serviceCategory): mixed
    {
        try {
            $serviceCategory->load('medicalSpecialty')->loadCount('services');

            return ApiResponse::success(
                new ServiceCategoryResource($serviceCategory),
                'Service category retrieved successfully'
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Failed to retrieve category: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Update service category.
     * PUT /api/v1/admin/service-categories/{id}
     */
    public function update(UpdateServiceCategoryRequest $request, ServiceCategory $serviceCategory): mixed
    {
        try {
            $updateData = [
                'name_ar' => $request->input('name_ar', $serviceCategory->name_ar),
                'name_en' => $request->input('name_en', $serviceCategory->name_en),
                'sort_order' => $request->input('sort_order', $serviceCategory->sort_order),
            ];

            if ($request->has('medical_specialty_id')) {
                $updateData['medical_specialty_id'] = $request->integer('medical_specialty_id');
            }

            if ($request->has('is_active')) {
                $updateData['is_active'] = $request->boolean('is_active');
            }

            $serviceCategory->update($updateData);
            $serviceCategory->load('medicalSpecialty');

            return ApiResponse::success(
                new ServiceCategoryResource($serviceCategory),
                'Service category updated successfully'
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Failed to update category: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Delete service category.
     * DELETE /api/v1/admin/service-categories/{id}
     */
    public function destroy(ServiceCategory $serviceCategory): mixed
    {
        try {
            if ($serviceCategory->services()->exists()) {
                return ApiResponse::error(
                    'Cannot delete category with associated services',
                    422
                );
            }

            $serviceCategory->delete();

            return ApiResponse::success(
                null,
                'Service category deleted successfully'
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Failed to delete category: ' . $e->getMessage(),
                500
            );
        }
    }
}

