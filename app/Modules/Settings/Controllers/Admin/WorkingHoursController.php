<?php

namespace App\Modules\Settings\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Settings\DTOs\WorkingHourDTO;
use App\Modules\Settings\Requests\StoreWorkingHourRequest;
use App\Modules\Settings\Requests\UpdateWorkingHourRequest;
use App\Modules\Settings\Resources\WorkingHourResource;
use App\Modules\Settings\Services\ClinicScheduleService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class WorkingHoursController extends Controller
{
    public function __construct(private readonly ClinicScheduleService $clinicScheduleService)
    {
    }

    public function index(): JsonResponse
    {
        $rows = $this->clinicScheduleService->getWorkingHours();

        return ApiResponse::success(
            WorkingHourResource::collection(collect($rows)),
            'Working hours retrieved.'
        );
    }

    public function store(StoreWorkingHourRequest $request): JsonResponse
    {
        $dto = WorkingHourDTO::fromArray($request->validated());
        $row = $this->clinicScheduleService->storeWorkingHour($dto);

        return ApiResponse::success(
            new WorkingHourResource($row),
            'Working hour created.',
            201
        );
    }

    public function update(UpdateWorkingHourRequest $request, int $id): JsonResponse
    {
        $dto = WorkingHourDTO::fromArray($request->validated());
        $row = $this->clinicScheduleService->updateWorkingHour($id, $dto);

        return ApiResponse::success(
            new WorkingHourResource($row),
            'Working hour updated.'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $this->clinicScheduleService->deleteWorkingHour($id);
        return ApiResponse::success(null, 'Working hour deleted.');
    }
}
