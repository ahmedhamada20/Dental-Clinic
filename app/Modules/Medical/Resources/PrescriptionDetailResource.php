<?php
// PrescriptionDetailResource.php
namespace App\Modules\Medical\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class PrescriptionDetailResource extends JsonResource {
    public function toArray(Request $request): array {
        return array_merge((new PrescriptionResource($this))->toArray($request), [
            'items'=>PrescriptionItemResource::collection($this->whenLoaded('items')),
        ]);
    }
}
