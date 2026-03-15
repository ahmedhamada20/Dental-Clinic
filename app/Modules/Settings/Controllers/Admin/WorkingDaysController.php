<?php

namespace App\Modules\Settings\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Settings\DTOs\WorkingDaysDTO;
use App\Modules\Settings\Requests\UpdateWorkingDaysRequest;
use App\Modules\Settings\Resources\WorkingDayResource;
use App\Modules\Settings\Services\ClinicScheduleService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class WorkingDaysController extends Controller
{
    public function __construct(private readonly ClinicScheduleService $clinicScheduleService)
    {
    }

    public function index(): JsonResponse
    {
        $data = $this->clinicScheduleService->getWorkingDays();
        return ApiResponse::success(new WorkingDayResource($data), 'Working days retrieved.');
    }

    public function update(UpdateWorkingDaysRequest $request): JsonResponse
    {
        $dto = WorkingDaysDTO::fromArray($request->validated());
        $data = $this->clinicScheduleService->updateWorkingDays($dto);

        return ApiResponse::success(new WorkingDayResource($data), 'Working days updated.');
    }
}
