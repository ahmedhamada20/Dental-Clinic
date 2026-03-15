<?php

namespace App\Modules\Dashboard\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Dashboard\Resources\DashboardSummaryResource;
use App\Modules\Dashboard\Resources\TodayQueueResource;
use App\Modules\Dashboard\Services\DashboardSummaryService;
use App\Modules\Dashboard\Services\TodayQueueService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardSummaryService $dashboardSummaryService,
        private readonly TodayQueueService $todayQueueService
    ) {
    }

    public function summary(): JsonResponse
    {
        $summary = $this->dashboardSummaryService->getSummary();

        return ApiResponse::success(
            new DashboardSummaryResource($summary),
            'Dashboard summary retrieved.'
        );
    }

    public function todayQueue(): JsonResponse
    {
        $rows = $this->todayQueueService->getQueue();

        return ApiResponse::success(
            TodayQueueResource::collection(collect($rows)),
            'Today queue retrieved.'
        );
    }
}
