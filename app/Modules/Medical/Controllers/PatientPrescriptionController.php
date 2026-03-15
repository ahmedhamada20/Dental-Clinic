<?php
// app/Modules/Medical/Controllers/PatientPrescriptionController.php
namespace App\Modules\Medical\Controllers;
use App\Models\Medical\Prescription;
use App\Modules\Medical\Resources\{PrescriptionDetailResource,PrescriptionResource};
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class PatientPrescriptionController {
    public function index(): JsonResponse {
        $patientId = \auth()->user()->id;
        $data = Prescription::query()->where('patient_id',$patientId)->latest('id')->get();
        return ApiResponse::success(PrescriptionResource::collection($data), 'Prescriptions retrieved successfully.');
    }
    public function show(int $id): JsonResponse {
        $patientId = \auth()->user()->id;
        $data = Prescription::query()->where('patient_id',$patientId)->with('items')->findOrFail($id);
        return ApiResponse::success(new PrescriptionDetailResource($data), 'Prescription retrieved successfully.');
    }
}
