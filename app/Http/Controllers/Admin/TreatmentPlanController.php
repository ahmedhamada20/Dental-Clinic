<?php

// app/Http/Controllers/Admin/TreatmentPlanController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Medical\TreatmentPlan;
use Illuminate\Http\Request;

class TreatmentPlanController extends Controller
{
    public function index(Request $request)
    {
        $query = TreatmentPlan::query();

        // Eager load common relations if they exist on the model.
        $relations = collect(['patient', 'doctor', 'items', 'visit'])
            ->filter(fn ($relation) => method_exists(TreatmentPlan::class, $relation))
            ->values()
            ->all();

        if (!empty($relations)) {
            $query->with($relations);
        }

        $treatmentPlans = $query
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.treatment-plans.index', compact('treatmentPlans'));
    }

    public function show(TreatmentPlan $treatmentPlan)
    {
        // Eager load common relations if they exist on the model.
        $relations = collect(['patient', 'doctor', 'items', 'visit'])
            ->filter(fn ($relation) => method_exists(TreatmentPlan::class, $relation))
            ->values()
            ->all();

        if (!empty($relations)) {
            $treatmentPlan->loadMissing($relations);
        }

        return view('admin.treatment-plans.show', compact('treatmentPlan'));
    }
}
