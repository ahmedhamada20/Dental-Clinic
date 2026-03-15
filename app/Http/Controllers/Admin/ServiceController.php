<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreServiceRequest;
use App\Http\Requests\Admin\UpdateServiceRequest;
use App\Models\Clinic\MedicalSpecialty;
use App\Models\Clinic\Service;
use App\Models\Clinic\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(Request $request): View
    {
        $services = Service::query()
            ->with('category.medicalSpecialty')
            ->when($request->filled('medical_specialty_id'), function ($query) use ($request) {
                $specialtyId = $request->integer('medical_specialty_id');
                $query->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('medical_specialty_id', $specialtyId));
            })
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $specialties = MedicalSpecialty::query()->where('is_active', true)->orderBy('name')->get();

        return view('admin.services.index', compact('services', 'specialties'));
    }

    public function create(Request $request): View
    {
        $selectedSpecialtyId = $request->integer('medical_specialty_id');

        $specialties = MedicalSpecialty::query()->where('is_active', true)->orderBy('name')->get();
        $categories = ServiceCategory::query()
            ->where('is_active', true)
            ->when($selectedSpecialtyId, fn ($query) => $query->where('medical_specialty_id', $selectedSpecialtyId))
            ->orderBy('sort_order')
            ->orderBy('name_en')
            ->get();

        return view('admin.services.create', compact('categories', 'specialties', 'selectedSpecialtyId'));
    }

    public function store(StoreServiceRequest $request): RedirectResponse
    {
        Service::create($request->validated());

        return redirect()
            ->route('admin.services.index')
            ->with('success', __('admin.messages.services.created'));
    }

    public function show(Service $service): View
    {
        $service->load(['category.medicalSpecialty', 'promotions']);

        return view('admin.services.show', compact('service'));
    }

    public function edit(Request $request, Service $service): View
    {
        $selectedSpecialtyId = $request->integer('medical_specialty_id')
            ?: $service->category?->medical_specialty_id;

        $specialties = MedicalSpecialty::query()->where('is_active', true)->orderBy('name')->get();
        $categories = ServiceCategory::query()
            ->where('is_active', true)
            ->when($selectedSpecialtyId, fn ($query) => $query->where('medical_specialty_id', $selectedSpecialtyId))
            ->orderBy('sort_order')
            ->orderBy('name_en')
            ->get();

        return view('admin.services.edit', compact('service', 'specialties', 'categories', 'selectedSpecialtyId'));
    }

    public function update(UpdateServiceRequest $request, Service $service): RedirectResponse
    {
        $service->update($request->validated());

        return redirect()
            ->route('admin.services.index')
            ->with('success', __('admin.messages.services.updated'));
    }

    public function destroy(Service $service): RedirectResponse
    {
        $service->delete();

        return redirect()
            ->route('admin.services.index')
            ->with('success', __('admin.messages.services.deleted'));
    }

    public function activate(Service $service): RedirectResponse
    {
        $service->update(['is_active' => true]);

        return back()->with('success', __('admin.messages.services.activated'));
    }

    public function deactivate(Service $service): RedirectResponse
    {
        $service->update(['is_active' => false]);

        return back()->with('success', __('admin.messages.services.deactivated'));
    }
}
