<?php
// app/Modules/Medical/Controllers/AdminMedicalFileController.php
namespace App\Modules\Medical\Controllers;
use App\Models\Medical\MedicalFile;
use App\Models\Patient\Patient;
use App\Modules\Medical\Actions\{DeleteMedicalFileAction,UploadMedicalFileAction};
use App\Modules\Medical\DTOs\{UpdateMedicalFileData,UploadMedicalFileData};
use App\Modules\Medical\Requests\{StoreMedicalFileRequest,UpdateMedicalFileRequest};
use App\Modules\Medical\Resources\MedicalFileResource;
use App\Modules\Medical\Services\MedicalFileService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AdminMedicalFileController {
    public function __construct(private readonly MedicalFileService $service) {}
    public function indexByPatient(int $id): JsonResponse {
        $patient = Patient::query()->findOrFail($id);
        return ApiResponse::success(MedicalFileResource::collection($this->service->byPatient($patient)), 'Medical files retrieved successfully.');
    }
    public function store(int $id, StoreMedicalFileRequest $request, UploadMedicalFileAction $action): JsonResponse {
        $patient = Patient::query()->findOrFail($id);
        $file = $action->execute($patient, new UploadMedicalFileData(
            file:$request->file('file'), fileCategory:$request->validated('file_category'),
            title:$request->validated('title'), notes:$request->validated('notes'),
            visitId:$request->validated('visit_id'),
            isVisibleToPatient:(bool)$request->validated('is_visible_to_patient', true)
        ), (int)Auth::id());
        return ApiResponse::success(new MedicalFileResource($file), 'Medical file uploaded successfully.');
    }
    public function show(int $id): JsonResponse {
        return ApiResponse::success(new MedicalFileResource($this->service->show($id)), 'Medical file retrieved successfully.');
    }
    public function update(int $id, UpdateMedicalFileRequest $request): JsonResponse {
        $file = MedicalFile::query()->findOrFail($id);
        $updated = $this->service->update($file, new UpdateMedicalFileData(
            fileCategory:$request->validated('file_category'), title:$request->validated('title'),
            notes:$request->validated('notes'), isVisibleToPatient:$request->validated('is_visible_to_patient'),
            visitId:$request->validated('visit_id')
        ));
        return ApiResponse::success(new MedicalFileResource($updated), 'Medical file updated successfully.');
    }
    public function destroy(int $id, DeleteMedicalFileAction $action): JsonResponse {
        $file = MedicalFile::query()->findOrFail($id); $action->execute($file);
        return ApiResponse::success(null, 'Medical file deleted successfully.');
    }
}
