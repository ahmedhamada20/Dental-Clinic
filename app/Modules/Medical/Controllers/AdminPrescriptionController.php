<?php
// app/Modules/Medical/Controllers/AdminPrescriptionController.php
namespace App\Modules\Medical\Controllers;
use App\Models\Medical\Prescription;
use App\Models\Medical\PrescriptionItem;
use App\Models\Visit\Visit;
use App\Modules\Medical\Actions\{AddPrescriptionItemAction,CreatePrescriptionAction};
use App\Modules\Medical\DTOs\{CreatePrescriptionData,PrescriptionItemData};
use App\Modules\Medical\Requests\{StorePrescriptionItemRequest,StorePrescriptionRequest,UpdatePrescriptionItemRequest,UpdatePrescriptionRequest};
use App\Modules\Medical\Resources\{PrescriptionDetailResource,PrescriptionItemResource};
use App\Modules\Medical\Services\PrescriptionService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AdminPrescriptionController {
    public function __construct(private readonly PrescriptionService $service) {}
    public function store(int $id, StorePrescriptionRequest $request, CreatePrescriptionAction $action): JsonResponse {
        $visit = Visit::query()->findOrFail($id);
        $prescription = $action->execute($visit, new CreatePrescriptionData($request->validated('notes')), (int)Auth::id());
        return ApiResponse::success(new PrescriptionDetailResource($prescription->load('items')), 'Prescription created successfully.');
    }
    public function show(int $id): JsonResponse { return ApiResponse::success(new PrescriptionDetailResource($this->service->show($id)), 'Prescription retrieved successfully.'); }
    public function update(int $id, UpdatePrescriptionRequest $request): JsonResponse {
        $prescription = Prescription::query()->findOrFail($id);
        return ApiResponse::success(new PrescriptionDetailResource($this->service->update($prescription, $request->validated('notes'))), 'Prescription updated successfully.');
    }
    public function addItem(int $id, StorePrescriptionItemRequest $request, AddPrescriptionItemAction $action): JsonResponse {
        $prescription = Prescription::query()->findOrFail($id);
        $item = $action->execute($prescription, new PrescriptionItemData(
            medicineName:$request->validated('medicine_name'), dosage:$request->validated('dosage'),
            frequency:$request->validated('frequency'), duration:$request->validated('duration'),
            instructions:$request->validated('instructions')
        ));
        return ApiResponse::success(new PrescriptionItemResource($item), 'Prescription item added successfully.');
    }
    public function updateItem(int $id, UpdatePrescriptionItemRequest $request): JsonResponse {
        $item = PrescriptionItem::query()->findOrFail($id);
        $updated = $this->service->updateItem($item, new PrescriptionItemData(
            medicineName:$request->validated('medicine_name'), dosage:$request->validated('dosage'),
            frequency:$request->validated('frequency'), duration:$request->validated('duration'),
            instructions:$request->validated('instructions')
        ));
        return ApiResponse::success(new PrescriptionItemResource($updated), 'Prescription item updated successfully.');
    }
}
