<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProviderController extends Controller
{
    public function index()
    {
        $providers = Provider::latest()->get();

        return Inertia::render('Settings/Providers/Index', [
            'providers' => $providers,
        ]);
    }

    public function create()
    {
        return Inertia::render('Settings/Providers/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:providers,name',
            'description' => 'nullable|string|max:1000',
        ]);

        Provider::create($validated);

        return redirect()->route('settings.providers.index')
            ->with('message', 'Provider created successfully.');
    }

    public function edit(Provider $provider)
    {
        return Inertia::render('Settings/Providers/Edit', [
            'provider' => $provider,
        ]);
    }

    public function update(Request $request, Provider $provider)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:providers,name,'.$provider->id,
            'description' => 'nullable|string|max:1000',
        ]);

        $provider->update($validated);

        return redirect()->route('settings.providers.index')
            ->with('message', 'Provider updated successfully.');
    }

    public function destroy(Provider $provider)
    {
        $provider->delete();

        return redirect()->route('settings.providers.index')
            ->with('message', 'Provider deleted successfully.');
    }
}
