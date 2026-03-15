<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreServiceCategoryRequest;
use App\Http\Requests\Admin\UpdateServiceCategoryRequest;
use App\Models\Clinic\MedicalSpecialty;
use App\Models\Clinic\ServiceCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
class ServiceCategoryController extends Controller
{
    public function index(): View
    {
        $categories = ServiceCategory::query()
            ->with('medicalSpecialty')
            ->withCount('services')
            ->when(request()->filled('medical_specialty_id'), fn ($query) => $query->where('medical_specialty_id', request()->integer('medical_specialty_id')))
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(15);

        $specialties = MedicalSpecialty::query()->where('is_active', true)->orderBy('name')->get();

        return view('admin.service-categories.index', compact('categories', 'specialties'));
    }
    public function create(): View
    {
        $specialties = MedicalSpecialty::query()->where('is_active', true)->orderBy('name')->get();

        return view('admin.service-categories.create', compact('specialties'));
    }
    public function store(StoreServiceCategoryRequest $request): RedirectResponse
    {
        ServiceCategory::query()->create($request->validated());
        return redirect()
            ->route('admin.service-categories.index')
            ->with('success', __('admin.service_categories.created'));
    }
    public function edit(ServiceCategory $serviceCategory): View
    {
        $specialties = MedicalSpecialty::query()->where('is_active', true)->orderBy('name')->get();

        return view('admin.service-categories.edit', [
            'category' => $serviceCategory,
            'specialties' => $specialties,
        ]);
    }
    public function update(UpdateServiceCategoryRequest $request, ServiceCategory $serviceCategory): RedirectResponse
    {
        $serviceCategory->update($request->validated());
        return redirect()
            ->route('admin.service-categories.index')
            ->with('success', __('admin.service_categories.updated'));
    }
    public function destroy(ServiceCategory $serviceCategory): RedirectResponse
    {
        if ($serviceCategory->services()->exists()) {
            return back()->with('error', __('admin.service_categories.cannot_delete_with_services'));
        }
        $serviceCategory->delete();
        return redirect()
            ->route('admin.service-categories.index')
            ->with('success', __('admin.service_categories.deleted'));
    }
    public function activate(ServiceCategory $serviceCategory): RedirectResponse
    {
        $serviceCategory->update(['is_active' => true]);
        return back()->with('success', __('admin.service_categories.activated'));
    }
    public function deactivate(ServiceCategory $serviceCategory): RedirectResponse
    {
        $serviceCategory->update(['is_active' => false]);
        return back()->with('success', __('admin.service_categories.deactivated'));
    }
}
