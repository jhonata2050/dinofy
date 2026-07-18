<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Services\DockerManager;
use App\Services\ResourceMonitor;
use App\Services\TenantProvisioner;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function index(Request $request)
    {
        $query = Tenant::with('plan');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('subdomain', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $tenants = $query->latest()->paginate(20);

        return view('tenants.index', compact('tenants'));
    }

    public function create()
    {
        $plans = Plan::where('is_active', true)->get();
        return view('tenants.create', compact('plans'));
    }

    public function store(Request $request, TenantProvisioner $provisioner)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'subdomain' => 'required|alpha_dash|unique:tenants,subdomain|max:32',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'document' => 'required|string|max:20',
            'password' => 'required|min:8',
            'notes' => 'nullable|string',
        ]);

        $credentials = TenantProvisioner::generateCredentials();
        $basePath = config('master.tenant_data_path');

        $tenant = Tenant::create([
            'plan_id' => $validated['plan_id'],
            'subdomain' => $validated['subdomain'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'document' => $validated['document'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'provisioning',
            'compose_project' => "dinofy-{$validated['subdomain']}",
            'data_path' => "{$basePath}/{$validated['subdomain']}",
            'db_password' => $credentials['db_password'],
            'app_key' => $credentials['app_key'],
        ]);

        TenantUser::create([
            'tenant_id' => $tenant->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'is_owner' => true,
        ]);

        try {
            $provisioner->provision($tenant);
            return redirect()->route('admin.tenants.show', $tenant)->with('success', 'Tenant provisionado com sucesso.');
        } catch (\Exception $e) {
            return redirect()->route('admin.tenants.show', $tenant)->with('error', "Erro ao provisionar: {$e->getMessage()}");
        }
    }

    public function show(Tenant $tenant, ResourceMonitor $monitor, DockerManager $docker)
    {
        $tenant->load(['plan', 'invoices' => fn ($q) => $q->latest()->take(10), 'domains']);
        $stats = $monitor->tenantStats($tenant);
        $logs = $docker->logs($tenant, 30);

        return view('tenants.show', compact('tenant', 'stats', 'logs'));
    }

    public function edit(Tenant $tenant)
    {
        $plans = Plan::where('is_active', true)->get();
        return view('tenants.edit', compact('tenant', 'plans'));
    }

    public function update(Request $request, Tenant $tenant, TenantProvisioner $provisioner)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'document' => 'required|string|max:20',
            'notes' => 'nullable|string',
            'password' => 'nullable|min:8',
        ]);

        $planChanged = $tenant->plan_id !== (int) $validated['plan_id'];

        if (!empty($validated['password'])) {
            $owner = $tenant->users()->where('is_owner', true)->first();
            if ($owner) {
                $owner->update(['password' => bcrypt($validated['password'])]);
            }
        }

        unset($validated['password']);
        $tenant->update($validated);

        if ($planChanged) {
            $provisioner->updatePlan($tenant);
        }

        return redirect()->route('admin.tenants.show', $tenant)->with('success', 'Tenant atualizado.');
    }

    public function suspend(Tenant $tenant, TenantProvisioner $provisioner)
    {
        $provisioner->suspend($tenant);
        return back()->with('success', 'Tenant suspenso.');
    }

    public function activate(Tenant $tenant, TenantProvisioner $provisioner)
    {
        $provisioner->activate($tenant);
        return back()->with('success', 'Tenant reativado.');
    }

    public function docker(Request $request, Tenant $tenant, DockerManager $docker)
    {
        $action = $request->route('action');

        $result = match ($action) {
            'start' => $docker->start($tenant),
            'stop' => $docker->stop($tenant),
            'restart' => (function () use ($docker, $tenant) {
                $docker->stop($tenant);
                return $docker->start($tenant);
            })(),
            default => ['success' => false, 'output' => 'Ação inválida'],
        };

        if ($result['success']) {
            return back()->with('success', "Docker: {$action} executado com sucesso.");
        }

        return back()->with('error', "Docker {$action} falhou: {$result['output']}");
    }

    public function reprovision(Tenant $tenant, TenantProvisioner $provisioner)
    {
        try {
            $provisioner->provision($tenant);
            return back()->with('success', 'Tenant reprovisionado com sucesso.');
        } catch (\Exception $e) {
            return back()->with('error', "Erro ao reprovisionar: {$e->getMessage()}");
        }
    }

    public function destroy(Tenant $tenant, TenantProvisioner $provisioner)
    {
        try {
            $provisioner->terminate($tenant);
        } catch (\Exception $e) {
            $tenant->update(['status' => 'terminated']);
        }
        return redirect()->route('admin.tenants.index')->with('success', 'Tenant destruído.');
    }
}
