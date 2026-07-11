<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Domain;
use App\Models\Tenant;
use Illuminate\Support\Str;

class TraefikManager
{
    public function addCustomDomain(Tenant $tenant, string $domain): Domain
    {
        $domainModel = Domain::create([
            'tenant_id' => $tenant->id,
            'domain' => strtolower(trim($domain)),
            'verification_token' => Str::random(32),
        ]);

        ActivityLog::log('domain.added', "Domínio {$domain} adicionado", $tenant->id);

        return $domainModel;
    }

    public function verifyDomain(Domain $domain): bool
    {
        $expected = $domain->verification_token;
        $records = @dns_get_record("_dinofy-verify.{$domain->domain}", DNS_TXT);

        if (!$records) {
            return false;
        }

        foreach ($records as $record) {
            if (($record['txt'] ?? '') === $expected) {
                $domain->update(['verified_at' => now()]);
                $this->applyDomain($domain);
                ActivityLog::log('domain.verified', "Domínio {$domain->domain} verificado", $domain->tenant_id);
                return true;
            }
        }

        return false;
    }

    public function applyDomain(Domain $domain): void
    {
        $tenant = $domain->tenant;
        $tenant->update(['custom_domain' => $domain->domain]);

        $generator = app(DockerComposeGenerator::class);
        $generator->saveToDisk($tenant);

        $docker = app(DockerManager::class);
        $docker->up($tenant);

        $domain->update(['ssl_provisioned' => true]);
        ActivityLog::log('domain.applied', "Domínio {$domain->domain} ativo com SSL", $domain->tenant_id);
    }

    public function removeDomain(Domain $domain): void
    {
        $tenant = $domain->tenant;

        if ($tenant->custom_domain === $domain->domain) {
            $tenant->update(['custom_domain' => null]);
            $generator = app(DockerComposeGenerator::class);
            $generator->saveToDisk($tenant);
            app(DockerManager::class)->up($tenant);
        }

        $domain->delete();
        ActivityLog::log('domain.removed', "Domínio {$domain->domain} removido", $tenant->id);
    }

    public function getVerificationInstructions(Domain $domain): array
    {
        return [
            'type' => 'TXT',
            'host' => "_dinofy-verify.{$domain->domain}",
            'value' => $domain->verification_token,
            'cname' => [
                'type' => 'CNAME',
                'host' => $domain->domain,
                'value' => "{$domain->tenant->subdomain}." . config('master.base_domain'),
            ],
        ];
    }
}
