<?php
// app/Modules/Medical/Controllers/AdminTreatmentPlanController.php
namespace App\Modules\Medical\Controllers;
use App\Models\Medical\TreatmentPlan;
use App\Models\Medical\TreatmentPlanItem;
use App\Models\Patient\Patient;
use App\Modules\Medical\Actions\{AddTreatmentPlanItemAction,CompleteTreatmentPlanItemAction,CreateTreatmentPlanAction,UpdateTreatmentPlanAction};
use App\Modules\Medical\DTOs\{CreateTreatmentPlanData,TreatmentPlanItemData,UpdateTreatmentPlanData};
use App\Modules\Medical\Requests\{ChangeTreatmentPlanStatusRequest,CompleteTreatmentPlanItemRequest,StoreTreatmentPlanItemRequest,StoreTreatmentPlanRequest,UpdateTreatmentPlanItemRequest,UpdateTreatmentPlanRequest};
use App\Modules\Medical\Resources\{TreatmentPlanDetailResource,TreatmentPlanItemResource,TreatmentPlanResource};
use App\Modules\Medical\Services\TreatmentPlanService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AdminTreatmentPlanController {
    public function __construct(private readonly TreatmentPlanService $service) {}
    public function indexByPatient(int $id): JsonResponse {
        $patient = Patient::query()->findOrFail($id);
        return ApiResponse::success(TreatmentPlanResource::collection($this->service->byPatient($patient)), 'Treatment plans retrieved successfully.');
    }
    public function store(int $id, StoreTreatmentPlanRequest $request, CreateTreatmentPlanAction $action): JsonResponse {
        $patient = Patient::query()->findOrFail($id);
        $plan = $action->execute($patient, new CreateTreatmentPlanData(
            title:$request->validated('title'), description:$request->validated('description'),
            estimatedTotal:(string)$request->validated('estimated_total'), status:$request->validated('status'),
            startDate:$request->validated('start_date'), endDate:$request->validated('end_date'), visitId:$request->validated('visit_id')
        ), (int)Auth::id());
        return ApiResponse::success(new TreatmentPlanDetailResource($plan), 'Treatment plan created successfully.');
    }
    public function show(int $id): JsonResponse {
        return ApiResponse::success(new TreatmentPlanDetailResource($this->service->show($id)), 'Treatment plan retrieved successfully.');
    }
    public function update(int $id, UpdateTreatmentPlanRequest $request, UpdateTreatmentPlanAction $action): JsonResponse {
        $plan = TreatmentPlan::query()->findOrFail($id);
        $updated = $action->execute($plan, new UpdateTreatmentPlanData(
            title:$request->validated('title'), description:$request->validated('description'),
            estimatedTotal:($request->has('estimated_total') ? (string)$request->validated('estimated_total') : null),
            status:$request->validated('status'), startDate:$request->validated('start_date'), endDate:$request->validated('end_date')
        ));
        return ApiResponse::success(new TreatmentPlanDetailResource($updated), 'Treatment plan updated successfully.');
    }
    public function changeStatus(int $id, ChangeTreatmentPlanStatusRequest $request): JsonResponse {
        $plan = TreatmentPlan::query()->findOrFail($id);
        $updated = $this->service->update($plan, new UpdateTreatmentPlanData(status:$request->validated('status')));
        return ApiResponse::success(new TreatmentPlanDetailResource($updated), 'Treatment plan status updated successfully.');
    }
    public function addItem(int $id, StoreTreatmentPlanItemRequest $request, AddTreatmentPlanItemAction $action): JsonResponse {
        $plan = TreatmentPlan::query()->findOrFail($id);
        $item = $action->execute($plan, new TreatmentPlanItemData(
            title:$request->validated('title'), serviceId:$request->validated('service_id'),
            toothNumber:$request->validated('tooth_number'), description:$request->validated('description'),
            sessionNo:$request->validated('session_no'),
            estimatedCost:($request->has('estimated_cost') ? (string)$request->validated('estimated_cost') : null),
            status:$request->validated('status'), plannedDate:$request->validated('planned_date')
        ));
        return ApiResponse::success(new TreatmentPlanItemResource($item), 'Treatment plan item added successfully.');
    }
    public function updateItem(int $id, UpdateTreatmentPlanItemRequest $request): JsonResponse {
        $item = TreatmentPlanItem::query()->findOrFail($id);
        $updated = $this->service->updateItem($item, new TreatmentPlanItemData(
            title:$request->validated('title') ?? $item->title, serviceId:$request->validated('service_id'),
            toothNumber:$request->validated('tooth_number'), description:$request->validated('description'),
            sessionNo:$request->validated('session_no'),
            estimatedCost:($request->has('estimated_cost') ? (string)$request->validated('estimated_cost') : null),
            status:$request->validated('status'), plannedDate:$request->validated('planned_date')
        ));
        return ApiResponse::success(new TreatmentPlanItemResource($updated), 'Treatment plan item updated successfully.');
    }
    public function completeItem(int $id, CompleteTreatmentPlanItemRequest $request, CompleteTreatmentPlanItemAction $action): JsonResponse {
        $item = TreatmentPlanItem::query()->findOrFail($id);
        $updated = $action->execute($item, $request->validated('completed_visit_id'));
        return ApiResponse::success(new TreatmentPlanItemResource($updated), 'Treatment plan item completed successfully.');
    }
}
