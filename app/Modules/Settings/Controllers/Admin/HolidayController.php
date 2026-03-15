<?php

namespace App\Modules\Settings\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Settings\DTOs\HolidayDTO;
use App\Modules\Settings\Requests\StoreHolidayRequest;
use App\Modules\Settings\Requests\UpdateHolidayRequest;
use App\Modules\Settings\Resources\HolidayResource;
use App\Modules\Settings\Services\ClinicScheduleService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class HolidayController extends Controller
{
    public function __construct(private readonly ClinicScheduleService $clinicScheduleService)
    {
    }

    public function index(): JsonResponse
    {
        $rows = $this->clinicScheduleService->getHolidays();

        return ApiResponse::success(
            HolidayResource::collection(collect($rows)),
            'Holidays retrieved.'
        );
    }

    public function store(StoreHolidayRequest $request): JsonResponse
    {
        $dto = HolidayDTO::fromArray($request->validated());
        $row = $this->clinicScheduleService->storeHoliday($dto);

        return ApiResponse::success(
            new HolidayResource($row),
            'Holiday created.',
            201
        );
    }

    public function update(UpdateHolidayRequest $request, int $id): JsonResponse
    {
        $dto = HolidayDTO::fromArray($request->validated());
        $row = $this->clinicScheduleService->updateHoliday($id, $dto);

        return ApiResponse::success(
            new HolidayResource($row),
            'Holiday updated.'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $this->clinicScheduleService->deleteHoliday($id);
        return ApiResponse::success(null, 'Holiday deleted.');
    }
}
