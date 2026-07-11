<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Services\TenantUpdater;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    public function index()
    {
        $tenants = Tenant::with('plan')->whereIn('status', ['active', 'suspended'])->get();
        $image = config('master.dinofy_image');

        return view('update.index', compact('tenants', 'image'));
    }

    public function pull(TenantUpdater $updater)
    {
        $result = $updater->pullImage();

        if ($result['success']) {
            return back()->with('success', "Imagem {$result['image']} atualizada com sucesso.");
        }

        return back()->with('error', "Erro ao baixar imagem: {$result['output']}");
    }

    public function deploy(TenantUpdater $updater)
    {
        $result = $updater->updateAll();

        if ($result['failed'] === 0) {
            return back()->with('success', "Todos os {$result['success']} tenants atualizados com sucesso.");
        }

        return back()->with('warning', "Update: {$result['success']} ok, {$result['failed']} falhas. Verifique os logs.");
    }

    public function updateTenant(Tenant $tenant, TenantUpdater $updater)
    {
        $result = $updater->updateTenant($tenant);

        if ($result['success']) {
            return back()->with('success', "Tenant {$tenant->subdomain} atualizado.");
        }

        return back()->with('error', "Falha ao atualizar {$tenant->subdomain}: " . ($result['error'] ?? 'Erro desconhecido'));
    }
}
