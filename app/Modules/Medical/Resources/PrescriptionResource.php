<?php
// PrescriptionResource.php
namespace App\Modules\Medical\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class PrescriptionResource extends JsonResource {
    public function toArray(Request $request): array {
        return [
            'id'=>$this->id,'patient_id'=>$this->patient_id,'visit_id'=>$this->visit_id,'doctor_id'=>$this->doctor_id,
            'notes'=>$this->notes,'issued_at'=>$this->issued_at,
        ];
    }
}
