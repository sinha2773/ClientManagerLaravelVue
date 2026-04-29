<?php

namespace App\Http\Controllers;

use App\Models\MarketingPartner;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MarketingPartnerController extends Controller
{
    public function index()
    {
        $partners = MarketingPartner::latest()->get();

        return Inertia::render('Settings/MarketingPartners/Index', [
            'partners' => $partners,
        ]);
    }

    public function create()
    {
        return Inertia::render('Settings/MarketingPartners/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:marketing_partners,name',
            'description' => 'nullable|string|max:1000',
        ]);

        MarketingPartner::create($validated);

        return redirect()->route('settings.marketing-partners.index')
            ->with('message', 'Marketing partner created successfully.');
    }

    public function edit(MarketingPartner $marketingPartner)
    {
        return Inertia::render('Settings/MarketingPartners/Edit', [
            'partner' => $marketingPartner,
        ]);
    }

    public function update(Request $request, MarketingPartner $marketingPartner)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:marketing_partners,name,'.$marketingPartner->id,
            'description' => 'nullable|string|max:1000',
        ]);

        $marketingPartner->update($validated);

        return redirect()->route('settings.marketing-partners.index')
            ->with('message', 'Marketing partner updated successfully.');
    }

    public function destroy(MarketingPartner $marketingPartner)
    {
        $marketingPartner->delete();

        return redirect()->route('settings.marketing-partners.index')
            ->with('message', 'Marketing partner deleted successfully.');
    }
}
