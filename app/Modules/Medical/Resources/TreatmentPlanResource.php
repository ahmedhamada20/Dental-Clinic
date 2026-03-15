<?php
// TreatmentPlanResource.php
namespace App\Modules\Medical\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class TreatmentPlanResource extends JsonResource {
    public function toArray(Request $request): array {
        return [
            'id'=>$this->id,'patient_id'=>$this->patient_id,'doctor_id'=>$this->doctor_id,'visit_id'=>$this->visit_id,
            'title'=>$this->title,'description'=>$this->description,'estimated_total'=>$this->estimated_total,
            'status'=>$this->status?->value ?? $this->status,'start_date'=>$this->start_date,'end_date'=>$this->end_date,
        ];
    }
}
