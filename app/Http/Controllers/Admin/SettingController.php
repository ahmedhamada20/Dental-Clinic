<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Clinic\ClinicSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $keys = [
            'clinic_name',
            'clinic_logo',
            'address',
            'phone',
            'email',
            'working_hours',
            'appointment_rules',
            'currency',
            'language',
            'timezone',
        ];

        $settings = [];

        foreach ($keys as $key) {
            $settings[$key] = ClinicSetting::getValue($key, '');
        }

        if (empty($settings['timezone'])) {
            $settings['timezone'] = config('app.timezone');
        }

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'clinic_name' => ['required', 'string', 'max:255'],
            'clinic_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,svg,webp', 'max:2048'],
            'address' => ['nullable', 'string', 'max:1000'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'working_hours' => ['nullable', 'string'],
            'appointment_rules' => ['nullable', 'string'],
            'currency' => ['required', 'string', 'max:10'],
            'language' => ['required', 'string', 'max:10'],
            'timezone' => ['required', 'timezone'],
        ]);

        if ($request->hasFile('clinic_logo')) {
            $oldLogo = ClinicSetting::getValue('clinic_logo');

            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }

            $validated['clinic_logo'] = $request->file('clinic_logo')->store('clinic', 'public');
        } else {
            unset($validated['clinic_logo']);
        }

        foreach ($validated as $key => $value) {
            ClinicSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Settings updated successfully.');
    }
}

