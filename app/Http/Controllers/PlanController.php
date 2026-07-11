<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::withCount('tenants')->get();
        return view('plans.index', compact('plans'));
    }

    public function create()
    {
        return view('plans.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|alpha_dash|unique:plans,slug|max:50',
            'cpu_limit' => 'required|numeric|min:0.25',
            'memory_limit' => 'required|string|max:10',
            'storage_limit_gb' => 'required|integer|min:1',
            'max_db_connections' => 'required|integer|min:10',
            'price_cents' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        Plan::create($validated);

        return redirect()->route('admin.plans.index')->with('success', 'Plano criado.');
    }

    public function edit(Plan $plan)
    {
        return view('plans.edit', compact('plan'));
    }

    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'cpu_limit' => 'required|numeric|min:0.25',
            'memory_limit' => 'required|string|max:10',
            'storage_limit_gb' => 'required|integer|min:1',
            'max_db_connections' => 'required|integer|min:10',
            'price_cents' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $plan->update($validated);

        return redirect()->route('admin.plans.index')->with('success', 'Plano atualizado.');
    }
}
