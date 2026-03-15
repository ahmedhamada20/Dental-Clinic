<?php
// PrescriptionItemResource.php
namespace App\Modules\Medical\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class PrescriptionItemResource extends JsonResource {
    public function toArray(Request $request): array {
        return [
            'id'=>$this->id,'prescription_id'=>$this->prescription_id,'medicine_name'=>$this->medicine_name,
            'dosage'=>$this->dosage,'frequency'=>$this->frequency,'duration'=>$this->duration,'instructions'=>$this->instructions,
        ];
    }
}
