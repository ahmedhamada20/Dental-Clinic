<?php
// app/Modules/Medical/Controllers/PatientMedicalFileController.php
namespace App\Modules\Medical\Controllers;
use App\Models\Medical\MedicalFile;
use App\Modules\Medical\Resources\MedicalFileResource;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class PatientMedicalFileController {
    public function index(): JsonResponse {
        $patientId = \auth()->user()->id;
        $files = MedicalFile::query()
            ->where('patient_id',$patientId)
            ->where('is_visible_to_patient', true)
            ->latest('id')->get();
        return ApiResponse::success(MedicalFileResource::collection($files), 'Medical files retrieved successfully.');
    }
    public function show(int $id): JsonResponse {
        $patientId = \auth()->user()->id;
        $file = MedicalFile::query()
            ->where('patient_id',$patientId)
            ->where('is_visible_to_patient', true)
            ->findOrFail($id);
        return ApiResponse::success(new MedicalFileResource($file), 'Medical file retrieved successfully.');
    }
}
