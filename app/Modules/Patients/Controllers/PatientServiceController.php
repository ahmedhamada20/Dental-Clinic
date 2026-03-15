<?php

namespace App\Modules\Patients\Controllers;

use App\Models\Clinic\Service;
use App\Support\ApiResponse;
use Illuminate\Routing\Controller;

class PatientServiceController extends Controller
{
    /**
     * List all available services for patients.
     * GET /api/v1/patient/services
     */
    public function index(): mixed
    {
        try {
            $search = request()->query('search');
            $categoryId = request()->query('category_id');
            $perPage = request()->query('per_page', 15);

            $query = Service::where('is_active', true)->where('is_bookable', true);

            // Search by name
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name_en', 'like', "%{$search}%")
                        ->orWhere('name_ar', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            }

            // Filter by category
            if ($categoryId) {
                $query->where('category_id', $categoryId);
            }

            $services = $query->with('category.medicalSpecialty.doctors')
                ->orderBy('sort_order')
                ->orderBy('name_en')
                ->paginate($perPage);



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
     * Get service details.
     * GET /api/v1/patient/services/{id}
     */
    public function show(Service $service): mixed
    {
        try {
            // Check if service is active and bookable
            if (!$service->is_active || !$service->is_bookable) {
                return ApiResponse::error('Service not available', 404);
            }

            $service->load('category.medicalSpecialty.doctors');

            return ApiResponse::success(
                $service,
                'Service retrieved successfully'
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Failed to retrieve service: ' . $e->getMessage(),
                500
            );
        }
    }
}

