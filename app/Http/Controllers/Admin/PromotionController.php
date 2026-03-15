<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePromotionRequest;
use App\Http\Requests\Admin\UpdatePromotionRequest;
use App\Models\Billing\Promotion;
use App\Models\Clinic\Service;
use App\Enums\PromotionType;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PromotionController extends Controller
{
    /**
     * Display a listing of the promotions.
     */
    public function index(): View
    {
        $promotions = Promotion::query()
            ->withCount('promotionServices')
            ->latest('id')
            ->paginate(15);

        return view('admin.promotions.index', compact('promotions'));
    }

    /**
     * Show the form for creating a new promotion.
     */
    public function create(): View
    {
        $services       = Service::active()->orderBy('name_en')->get();
        $promotionTypes = PromotionType::options();

        return view('admin.promotions.create', compact('services', 'promotionTypes'));
    }

    /**
     * Store a newly created promotion in storage.
     */
    public function store(StorePromotionRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $serviceIds = $data['service_ids'] ?? [];
        unset($data['service_ids']);

        $promotion = Promotion::create($data);

        if (!empty($serviceIds)) {
            $promotion->services()->sync($serviceIds);
        }

        return redirect()
            ->route('admin.promotions.index')
            ->with('success', __('admin.messages.promotions.created'));
    }

    /**
     * Display the specified promotion.
     */
    public function show(Promotion $promotion): View
    {
        $promotion->load('services.category');

        return view('admin.promotions.show', compact('promotion'));
    }

    /**
     * Show the form for editing the specified promotion.
     */
    public function edit(Promotion $promotion): View
    {
        $services           = Service::active()->orderBy('name_en')->get();
        $promotionTypes     = PromotionType::options();
        $selectedServiceIds = $promotion->services()->pluck('services.id')->toArray();

        return view('admin.promotions.edit', compact('promotion', 'services', 'promotionTypes', 'selectedServiceIds'));
    }

    /**
     * Update the specified promotion in storage.
     */
    public function update(UpdatePromotionRequest $request, Promotion $promotion): RedirectResponse
    {
        $data = $request->validated();
        $serviceIds = $data['service_ids'] ?? [];
        unset($data['service_ids']);

        $promotion->update($data);
        $promotion->services()->sync($serviceIds);

        return redirect()
            ->route('admin.promotions.index')
            ->with('success', __('admin.messages.promotions.updated'));
    }

    /**
     * Remove the specified promotion from storage.
     */
    public function destroy(Promotion $promotion): RedirectResponse
    {
        $promotion->services()->detach();
        $promotion->delete();

        return redirect()
            ->route('admin.promotions.index')
            ->with('success', __('admin.messages.promotions.deleted'));
    }

    /**
     * Activate the specified promotion.
     */
    public function activate(Promotion $promotion): RedirectResponse
    {
        $promotion->update(['is_active' => true]);

        return back()->with('success', __('admin.messages.promotions.activated'));
    }

    /**
     * Deactivate the specified promotion.
     */
    public function deactivate(Promotion $promotion): RedirectResponse
    {
        $promotion->update(['is_active' => false]);

        return back()->with('success', __('admin.messages.promotions.deactivated'));
    }
}
