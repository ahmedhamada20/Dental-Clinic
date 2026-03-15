<?php

namespace App\Modules\Visits\Controllers;

use App\Models\Visit\Visit;
use App\Modules\Visits\Actions\CompleteVisitAction;
use App\Modules\Visits\Actions\StartVisitAction;
use App\Modules\Visits\DTOs\CompleteVisitData;
use App\Modules\Visits\DTOs\StartVisitData;
use App\Modules\Visits\Requests\CompleteVisitRequest;
use App\Modules\Visits\Requests\StartVisitRequest;
use App\Modules\Visits\Resources\VisitDetailResource;
use App\Modules\Visits\Resources\VisitResource;
use App\Modules\Visits\Services\VisitService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminVisitController
{
    public function __construct(private readonly VisitService $visitService) {}

    public function index(Request $request): JsonResponse
    {
        $visits = $this->visitService->paginate((int) $request->get('per_page', 15));

        return ApiResponse::paginated(
            VisitResource::collection($visits),
            'Visits retrieved successfully.'
        );
    }

    public function show(int $id): JsonResponse
    {
        $visit = $this->visitService->findOrFail($id);

        return ApiResponse::success(
            new VisitDetailResource($visit),
            'Visit retrieved successfully.'
        );
    }

    public function start(
        int $id,
        StartVisitRequest $request,
        StartVisitAction $action
    ): JsonResponse {
        $visit = Visit::query()->findOrFail($id);

        $updated = $action->execute(
            $visit,
            new StartVisitData($request->validated('clinical_notes'))
        );

        return ApiResponse::success(
            new VisitDetailResource($updated),
            'Visit started successfully.'
        );
    }

    public function complete(
        int $id,
        CompleteVisitRequest $request,
        CompleteVisitAction $action
    ): JsonResponse {
        $visit = Visit::query()->findOrFail($id);

        $updated = $action->execute(
            $visit,
            new CompleteVisitData(
                diagnosis: $request->validated('diagnosis'),
                clinicalNotes: $request->validated('clinical_notes'),
                internalNotes: $request->validated('internal_notes')
            )
        );

        return ApiResponse::success(
            new VisitDetailResource($updated),
            'Visit completed successfully.'
        );
    }
}
