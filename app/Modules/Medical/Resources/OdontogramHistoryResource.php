<?php
// OdontogramHistoryResource.php
namespace App\Modules\Medical\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class OdontogramHistoryResource extends JsonResource {
    public function toArray(Request $request): array {
        return [
            'id'=>$this->id,'patient_id'=>$this->patient_id,'tooth_number'=>$this->tooth_number,
            'old_status'=>$this->old_status,'new_status'=>$this->new_status,'surface'=>$this->surface,
            'notes'=>$this->notes,'visit_id'=>$this->visit_id,'changed_by'=>$this->changed_by,'created_at'=>$this->created_at,
        ];
    }
}
