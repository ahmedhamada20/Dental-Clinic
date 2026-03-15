<?php
// app/Modules/Medical/Services/TreatmentPlanService.php
namespace App\Modules\Medical\Services;
use App\Models\Medical\TreatmentPlan;
use App\Models\Medical\TreatmentPlanItem;
use App\Models\Patient\Patient;
use App\Modules\Medical\DTOs\CreateTreatmentPlanData;
use App\Modules\Medical\DTOs\TreatmentPlanItemData;
use App\Modules\Medical\DTOs\UpdateTreatmentPlanData;

class TreatmentPlanService {
    public function byPatient(Patient $patient){ return $patient->treatmentPlans()->with('items')->latest('id')->get(); }
    public function show(int $id): TreatmentPlan { return TreatmentPlan::query()->with('items')->findOrFail($id); }

    public function create(Patient $patient, CreateTreatmentPlanData $data, int $doctorId): TreatmentPlan {
        return TreatmentPlan::query()->create([
            'patient_id'=>$patient->id,'doctor_id'=>$doctorId,'visit_id'=>$data->visitId,'title'=>$data->title,
            'description'=>$data->description,'estimated_total'=>$data->estimatedTotal,'status'=>$data->status,
            'start_date'=>$data->startDate,'end_date'=>$data->endDate,
        ])->load('items');
    }

    public function update(TreatmentPlan $plan, UpdateTreatmentPlanData $data): TreatmentPlan {
        $payload = array_filter([
            'title'=>$data->title,'description'=>$data->description,'estimated_total'=>$data->estimatedTotal,
            'status'=>$data->status,'start_date'=>$data->startDate,'end_date'=>$data->endDate,
        ], static fn($v)=>$v!==null);
        $plan->update($payload);
        return $plan->refresh()->load('items');
    }

    public function addItem(TreatmentPlan $plan, TreatmentPlanItemData $data): TreatmentPlanItem {
        return $plan->items()->create([
            'service_id'=>$data->serviceId,'tooth_number'=>$data->toothNumber,'title'=>$data->title,
            'description'=>$data->description,'session_no'=>$data->sessionNo,'estimated_cost'=>$data->estimatedCost,
            'status'=>$data->status,'planned_date'=>$data->plannedDate,
        ]);
    }

    public function updateItem(TreatmentPlanItem $item, TreatmentPlanItemData $data): TreatmentPlanItem {
        $item->update(array_filter([
            'service_id'=>$data->serviceId,'tooth_number'=>$data->toothNumber,'title'=>$data->title,
            'description'=>$data->description,'session_no'=>$data->sessionNo,'estimated_cost'=>$data->estimatedCost,
            'status'=>$data->status,'planned_date'=>$data->plannedDate,
        ], static fn($v)=>$v!==null));
        return $item->refresh();
    }

    public function completeItem(TreatmentPlanItem $item, ?int $completedVisitId = null): TreatmentPlanItem {
        $item->update(['status'=>'completed','completed_visit_id'=>$completedVisitId]);
        return $item->refresh();
    }
}
