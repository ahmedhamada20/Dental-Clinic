<?php
// OdontogramToothResource.php
namespace App\Modules\Medical\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class OdontogramToothResource extends JsonResource {
    public function toArray(Request $request): array {
        return [
            'id'=>$this->id,'patient_id'=>$this->patient_id,'tooth_number'=>$this->tooth_number,
            'status'=>$this->status?->value ?? $this->status,'surface'=>$this->surface,'notes'=>$this->notes,
            'visit_id'=>$this->visit_id,'last_updated_by'=>$this->last_updated_by,'updated_at'=>$this->updated_at,
        ];
    }
}
