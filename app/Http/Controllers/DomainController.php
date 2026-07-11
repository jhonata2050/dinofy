<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Models\Tenant;
use App\Services\TraefikManager;
use Illuminate\Http\Request;

class DomainController extends Controller
{
    public function store(Request $request, Tenant $tenant, TraefikManager $traefik)
    {
        $validated = $request->validate([
            'domain' => 'required|string|max:255|unique:domains,domain',
        ]);

        $domain = $traefik->addCustomDomain($tenant, $validated['domain']);
        $instructions = $traefik->getVerificationInstructions($domain);

        return back()->with('success', 'Domínio adicionado. Configure os registros DNS.')->with('dns_instructions', $instructions);
    }

    public function verify(Domain $domain, TraefikManager $traefik)
    {
        $verified = $traefik->verifyDomain($domain);

        if ($verified) {
            return back()->with('success', 'Domínio verificado e SSL ativado.');
        }

        return back()->with('error', 'Registro DNS TXT não encontrado. Verifique a configuração.');
    }

    public function destroy(Domain $domain, TraefikManager $traefik)
    {
        $traefik->removeDomain($domain);
        return back()->with('success', 'Domínio removido.');
    }
}
