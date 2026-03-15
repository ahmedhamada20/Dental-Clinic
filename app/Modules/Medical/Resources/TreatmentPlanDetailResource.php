<?php
// TreatmentPlanDetailResource.php
namespace App\Modules\Medical\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class TreatmentPlanDetailResource extends JsonResource {
    public function toArray(Request $request): array {
        return array_merge((new TreatmentPlanResource($this))->toArray($request), [
            'items'=>TreatmentPlanItemResource::collection($this->whenLoaded('items')),
        ]);
    }
}
