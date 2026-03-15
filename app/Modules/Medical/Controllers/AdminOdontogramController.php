<?php
// app/Modules/Medical/Controllers/AdminOdontogramController.php
namespace App\Modules\Medical\Controllers;
use App\Models\Patient\Patient;
use App\Modules\Medical\Actions\UpdateToothStatusAction;
use App\Modules\Medical\DTOs\UpdateToothStatusData;
use App\Modules\Medical\Requests\UpdateOdontogramToothRequest;
use App\Modules\Medical\Resources\OdontogramHistoryResource;
use App\Modules\Medical\Resources\OdontogramToothResource;
use App\Modules\Medical\Services\OdontogramService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AdminOdontogramController {
    public function __construct(private readonly OdontogramService $service) {}
    public function index(int $id): JsonResponse {
        $patient = Patient::query()->findOrFail($id);
        return ApiResponse::success(OdontogramToothResource::collection($this->service->listTeeth($patient)), 'Odontogram retrieved successfully.');
    }
    public function updateTooth(int $id, UpdateOdontogramToothRequest $request, UpdateToothStatusAction $action): JsonResponse {
        $patient = Patient::query()->findOrFail($id);
        $tooth = $action->execute($patient, new UpdateToothStatusData(
            toothNumber:(int)$request->validated('tooth_number'),
            status:$request->validated('status'),
            surface:$request->validated('surface'),
            notes:$request->validated('notes'),
            visitId:$request->validated('visit_id')
        ), (int)Auth::id());
        return ApiResponse::success(new OdontogramToothResource($tooth), 'Tooth status updated successfully.');
    }
    public function history(int $id): JsonResponse {
        $patient = Patient::query()->findOrFail($id);
        return ApiResponse::success(OdontogramHistoryResource::collection($this->service->listHistory($patient)), 'Odontogram history retrieved successfully.');
    }
}
