<?php

namespace App\Modules\Settings\Controllers;

use App\Models\Clinic\Service;
use App\Modules\Settings\Requests\StoreServiceRequest;
use App\Modules\Settings\Requests\UpdateServiceRequest;
use App\Modules\Settings\Resources\ServiceResource;
use App\Support\ApiResponse;
use Illuminate\Routing\Controller;

class ServiceController extends Controller
{
    /**
     * List all services.
     * GET /api/v1/admin/services
     */
    public function index(): mixed
    {
        try {
            $search = request()->query('search');
            $categoryId = request()->query('category_id');
            $medicalSpecialtyId = request()->query('medical_specialty_id');
            $isActive = request()->query('is_active');
            $isBookable = request()->query('is_bookable');
            $perPage = request()->query('per_page', 15);
            $sortBy = request()->query('sort_by', 'sort_order');
            $sortDirection = request()->query('sort_direction', 'asc');

            $query = Service::query()->with('category.medicalSpecialty');

            // Search
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name_en', 'like', "%{$search}%")
                        ->orWhere('name_ar', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            }

            // Filter by specialty
            if ($medicalSpecialtyId) {
                $query->whereHas('category', function ($q) use ($medicalSpecialtyId) {
                    $q->where('medical_specialty_id', $medicalSpecialtyId);
                });
            }

            // Filter by category
            if ($categoryId) {
                $query->where('category_id', $categoryId);
            }

            // Filter by active status
            if ($isActive !== null) {
                $query->where('is_active', filter_var($isActive, FILTER_VALIDATE_BOOLEAN));
            }

            // Filter by bookable status
            if ($isBookable !== null) {
                $query->where('is_bookable', filter_var($isBookable, FILTER_VALIDATE_BOOLEAN));
            }

            // Sort
            if (in_array($sortBy, ['sort_order', 'name_en', 'default_price', 'created_at'])) {
                $query->orderBy($sortBy, $sortDirection);
            } else {
                $query->orderBy('sort_order')->orderBy('name_en');
            }

            $services = $query->paginate($perPage);

            return ApiResponse::paginated(
                $services,
                'Services retrieved successfully',
                200
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Failed to retrieve services: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Create a new service.
     * POST /api/v1/admin/services
     */
    public function store(StoreServiceRequest $request): mixed
    {
        try {
            $service = Service::create([
                'category_id' => $request->input('category_id'),
                'code' => $request->input('code'),
                'name_ar' => $request->input('name_ar'),
                'name_en' => $request->input('name_en'),
                'description_ar' => $request->input('description_ar'),
                'description_en' => $request->input('description_en'),
                'default_price' => $request->input('default_price'),
                'duration_minutes' => $request->input('duration_minutes'),
                'is_bookable' => $request->boolean('is_bookable', true),
                'is_active' => $request->boolean('is_active', true),
                'sort_order' => $request->input('sort_order', 0),
            ]);

            $service->load('category.medicalSpecialty');

            return ApiResponse::success(
                new ServiceResource($service),
                'Service created successfully',
                201
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Failed to create service: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Get service details.
     * GET /api/v1/admin/services/{id}
     */
    public function show(Service $service): mixed
    {
        try {
            $service->load('category.medicalSpecialty');

            return ApiResponse::success(
                new ServiceResource($service),
                'Service retrieved successfully'
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Failed to retrieve service: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Update service.
     * PUT /api/v1/admin/services/{id}
     */
    public function update(UpdateServiceRequest $request, Service $service): mixed
    {
        try {
            $updateData = [
                'category_id' => $request->input('category_id', $service->category_id),
                'code' => $request->input('code', $service->code),
                'name_ar' => $request->input('name_ar', $service->name_ar),
                'name_en' => $request->input('name_en', $service->name_en),
                'description_ar' => $request->input('description_ar', $service->description_ar),
                'description_en' => $request->input('description_en', $service->description_en),
                'default_price' => $request->input('default_price', $service->default_price),
                'duration_minutes' => $request->input('duration_minutes', $service->duration_minutes),
                'sort_order' => $request->input('sort_order', $service->sort_order),
            ];

            if ($request->has('is_bookable')) {
                $updateData['is_bookable'] = $request->boolean('is_bookable');
            }

            if ($request->has('is_active')) {
                $updateData['is_active'] = $request->boolean('is_active');
            }

            $service->update($updateData);
            $service->load('category.medicalSpecialty');

            return ApiResponse::success(
                new ServiceResource($service),
                'Service updated successfully'
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Failed to update service: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Toggle service status (active/inactive).
     * POST /api/v1/admin/services/{id}/toggle-status
     */
    public function toggleStatus(Service $service): mixed
    {
        try {
            $service->update([
                'is_active' => !$service->is_active,
            ]);

            $service->load('category.medicalSpecialty');

            return ApiResponse::success(
                new ServiceResource($service),
                'Service status toggled successfully'
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Failed to toggle status: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Delete service.
     * DELETE /api/v1/admin/services/{id}
     */
    public function destroy(Service $service): mixed
    {
        try {
            $service->delete();

            return ApiResponse::success(
                null,
                'Service deleted successfully'
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Failed to delete service: ' . $e->getMessage(),
                500
            );
        }
    }
}

