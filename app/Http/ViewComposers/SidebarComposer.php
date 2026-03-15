<?php

namespace App\Http\ViewComposers;

use App\Models\Visit\Visit;
use Illuminate\View\View;

class SidebarComposer
{
    public function compose(View $view): void
    {
        $view->with('sidebarTodayVisits', Visit::query()
            ->whereDate('visit_date', today())
            ->with(['patient:id,full_name,first_name,last_name', 'doctor:id,full_name,first_name,last_name'])
            ->orderBy('start_at')
            ->orderBy('id')
            ->get(['id', 'visit_no', 'patient_id', 'doctor_id', 'start_at', 'status'])
        );
    }
}

