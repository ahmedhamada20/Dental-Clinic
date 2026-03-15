<?php

namespace App\Modules\Settings\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Settings\DTOs\ClinicSettingDTO;
use App\Modules\Settings\Requests\UpdateClinicSettingRequest;
use App\Modules\Settings\Resources\ClinicSettingResource;
use App\Modules\Settings\Services\ClinicScheduleService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class ClinicSettingsController extends Controller
{
    public function __construct(private readonly ClinicScheduleService $clinicScheduleService)
    {
    }

    public function show(): JsonResponse
    {
        $data = $this->clinicScheduleService->getClinicSettings();
        return ApiResponse::success(new ClinicSettingResource($data), 'Clinic settings retrieved.');
    }

    public function update(UpdateClinicSettingRequest $request): JsonResponse
    {
        $dto = ClinicSettingDTO::fromArray($request->validated());
        $data = $this->clinicScheduleService->updateClinicSettings($dto);

        return ApiResponse::success(new ClinicSettingResource($data), 'Clinic settings updated.');
    }
}
