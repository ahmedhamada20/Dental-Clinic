<?php
// TreatmentPlanItemResource.php
namespace App\Modules\Medical\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class TreatmentPlanItemResource extends JsonResource {
    public function toArray(Request $request): array {
        return [
            'id'=>$this->id,'treatment_plan_id'=>$this->treatment_plan_id,'service_id'=>$this->service_id,
            'tooth_number'=>$this->tooth_number,'title'=>$this->title,'description'=>$this->description,
            'session_no'=>$this->session_no,'estimated_cost'=>$this->estimated_cost,
            'status'=>$this->status?->value ?? $this->status,'planned_date'=>$this->planned_date,
            'completed_visit_id'=>$this->completed_visit_id,
        ];
    }
}
