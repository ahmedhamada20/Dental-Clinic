<?php

namespace App\Modules\Notifications\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Patient\Patient;
use App\Modules\Notifications\DTOs\NotificationListFilterDTO;
use App\Modules\Notifications\DTOs\RegisterDeviceTokenDTO;
use App\Modules\Notifications\Requests\PatientNotificationListRequest;
use App\Modules\Notifications\Requests\RegisterDeviceTokenRequest;
use App\Modules\Notifications\Resources\DeviceTokenResource;
use App\Modules\Notifications\Resources\NotificationResource;
use App\Modules\Notifications\Services\NotificationService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientNotificationController extends Controller
{
    public function __construct(private readonly NotificationService $notificationService)
    {
    }

    public function index(PatientNotificationListRequest $request): JsonResponse
    {
        $patient =auth()->user();
        $dto = NotificationListFilterDTO::fromArray($request->validated());

        $paginator = $this->notificationService->listForPatient((int) $patient->id, $dto);

        return ApiResponse::success([
            'items' => NotificationResource::collection(collect($paginator->items())),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ], 'Notifications retrieved successfully.');
    }


}
