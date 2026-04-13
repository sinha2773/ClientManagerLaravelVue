<?php

namespace App\Http\Controllers;

use App\Models\ClientCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClientCategoryController extends Controller
{
    public function index()
    {
        $categories = ClientCategory::latest()->get();

        return Inertia::render('Settings/ClientCategories/Index', [
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        return Inertia::render('Settings/ClientCategories/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:client_categories,name',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
        ]);

        ClientCategory::create($validated);

        return redirect()->route('settings.client-categories.index')
            ->with('message', 'Client category created successfully.');
    }

    public function edit(ClientCategory $clientCategory)
    {
        return Inertia::render('Settings/ClientCategories/Edit', [
            'category' => $clientCategory,
        ]);
    }

    public function update(Request $request, ClientCategory $clientCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:client_categories,name,'.$clientCategory->id,
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
        ]);

        $clientCategory->update($validated);

        return redirect()->route('settings.client-categories.index')
            ->with('message', 'Client category updated successfully.');
    }

    public function destroy(ClientCategory $clientCategory)
    {
        $clientCategory->delete();

        return redirect()->route('settings.client-categories.index')
            ->with('message', 'Client category deleted successfully.');
    }
}
