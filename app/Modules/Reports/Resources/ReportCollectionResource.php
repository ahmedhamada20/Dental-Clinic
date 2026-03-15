<?php

namespace App\Modules\Reports\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportCollectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'title' => $this['title'] ?? null,
            'filters' => $this['filters'] ?? [],
            'summary' => $this['summary'] ?? [],
            'rows' => $this['rows'] ?? [],
            'analytics' => $this['analytics'] ?? [],
        ];
    }
}
