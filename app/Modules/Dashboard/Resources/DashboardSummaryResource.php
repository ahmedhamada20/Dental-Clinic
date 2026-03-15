<?php

namespace App\Modules\Dashboard\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'today_appointments' => (int) ($this['today_appointments'] ?? 0),
            'checked_in_count' => (int) ($this['checked_in_count'] ?? 0),
            'completed_visits' => (int) ($this['completed_visits'] ?? 0),
            'today_revenue' => (float) ($this['today_revenue'] ?? 0),
            'pending_invoices' => (int) ($this['pending_invoices'] ?? 0),
            'waiting_list_count' => (int) ($this['waiting_list_count'] ?? 0),
        ];
    }
}
