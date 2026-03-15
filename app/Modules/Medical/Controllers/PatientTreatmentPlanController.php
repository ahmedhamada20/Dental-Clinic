<?php
// app/Modules/Medical/Controllers/PatientTreatmentPlanController.php
namespace App\Modules\Medical\Controllers;
use App\Models\Medical\TreatmentPlan;
use App\Modules\Medical\Resources\{TreatmentPlanDetailResource,TreatmentPlanResource};
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class PatientTreatmentPlanController {
    public function index(): JsonResponse {

        $patientId = \auth()->user()->id;
        $plans = \App\Models\Medical\TreatmentPlan::query()->where('patient_id',$patientId)->latest('id')->get();
        return ApiResponse::success(TreatmentPlanResource::collection($plans), 'Treatment plans retrieved successfully.');
    }
    public function show(int $id): JsonResponse {
        $patientId = \auth()->user()->id;
        $plan = TreatmentPlan::query()->where('patient_id',$patientId)->with('items')->findOrFail($id);
        return ApiResponse::success(new TreatmentPlanDetailResource($plan), 'Treatment plan retrieved successfully.');
    }
}
