<?php
// MedicalFileResource.php
namespace App\Modules\Medical\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
class MedicalFileResource extends JsonResource {
    public function toArray(Request $request): array {
        return [
            'id'=>$this->id,'patient_id'=>$this->patient_id,'visit_id'=>$this->visit_id,'uploaded_by'=>$this->uploaded_by,
            'file_category'=>$this->file_category?->value ?? $this->file_category,'title'=>$this->title,'notes'=>$this->notes,
            'file_name'=>$this->file_name,'file_extension'=>$this->file_extension,'mime_type'=>$this->mime_type,'file_size'=>$this->file_size,
            'is_visible_to_patient'=>$this->is_visible_to_patient,'uploaded_at'=>$this->uploaded_at,
            'file_url'=>Storage::disk('public')->url($this->file_path),
        ];
    }
}
