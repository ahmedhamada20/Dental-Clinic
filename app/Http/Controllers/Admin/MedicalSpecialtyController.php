<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AttachDoctorToSpecialtyRequest;
use App\Models\Clinic\MedicalSpecialty;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MedicalSpecialtyController extends Controller
{
    public function index(Request $request): View
    {

        $query = MedicalSpecialty::query()->withCount(['doctors', 'serviceCategories']);

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('search')) {
            $term = trim((string) $request->input('search'));
            $query->where(function ($builder) use ($term) {
                $builder->where('name', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
        }

        $specialties = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.specialties.index', compact('specialties'));
    }

    public function create(): View
    {
        return view('admin.specialties.create');
    }

    public function show(MedicalSpecialty $specialty): View
    {
        $specialty->load([
            'doctors' => function ($query) {
                $query->with('specialty:id,name')
                    ->orderBy('full_name')
                    ->orderBy('first_name')
                    ->orderBy('last_name');
            },
        ])->loadCount(['doctors', 'serviceCategories']);

        $availableDoctors = User::query()
            ->with('specialty:id,name')
            ->where('user_type', UserType::DOCTOR->value)
            ->where(function ($query) use ($specialty) {
                $query->whereNull('specialty_id')
                    ->orWhere('specialty_id', '!=', $specialty->id);
            })
            ->orderBy('full_name')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('admin.specialties.show', compact('specialty', 'availableDoctors'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:medical_specialties,name'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        MedicalSpecialty::query()->create($validated);

        return redirect()->route('admin.specialties.index')
            ->with('success', __('admin.specialties.created'));
    }

    public function edit(MedicalSpecialty $specialty): View
    {
        return view('admin.specialties.edit', compact('specialty'));
    }

    public function update(Request $request, MedicalSpecialty $specialty): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:medical_specialties,name,' . $specialty->id],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $specialty->update($validated);

        return redirect()->route('admin.specialties.index')
            ->with('success', __('admin.specialties.updated'));
    }

    public function activate(MedicalSpecialty $specialty): RedirectResponse
    {
        $specialty->update(['is_active' => true]);

        return back()->with('success', __('admin.specialties.activated'));
    }

    public function deactivate(MedicalSpecialty $specialty): RedirectResponse
    {
        $specialty->update(['is_active' => false]);

        return back()->with('success', __('admin.specialties.deactivated'));
    }

    public function attachDoctor(AttachDoctorToSpecialtyRequest $request, MedicalSpecialty $specialty): RedirectResponse
    {
        $doctor = User::query()
            ->whereKey($request->integer('doctor_id'))
            ->where('user_type', UserType::DOCTOR->value)
            ->firstOrFail();

        $doctor->update([
            'specialty_id' => $specialty->id,
        ]);

        return redirect()
            ->route('admin.specialties.show', $specialty)
            ->with('success', __('admin.specialties.doctor_assigned', [
                'doctor' => $doctor->display_name,
                'specialty' => $specialty->name,
            ]));
    }
}

