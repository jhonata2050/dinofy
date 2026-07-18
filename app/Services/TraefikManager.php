<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Domain;
use App\Models\Tenant;
use Illuminate\Support\Str;

class TraefikManager
{
    private string $configPath = '/etc/traefik/dynamic';

    public function writeTenantConfig(Tenant $tenant): void
    {
        if (!is_dir($this->configPath)) {
            return;
        }

        $project = $tenant->compose_project;
        $subdomain = $tenant->subdomain;
        $baseDomain = config('master.base_domain');
        $containerUrl = "http://{$project}-app-1:80";

        $routers = [];
        $routers["{$project}"] = [
            'rule' => "Host(`{$subdomain}.{$baseDomain}`)",
            'service' => $project,
            'entryPoints' => ['web'],
            'priority' => 10,
        ];

        $verifiedDomains = $tenant->domains()->whereNotNull('verified_at')->get();
        foreach ($verifiedDomains as $domain) {
            $slug = Str::slug($domain->domain);
            $routers["{$project}-{$slug}"] = [
                'rule' => "Host(`{$domain->domain}`)",
                'service' => $project,
                'entryPoints' => ['web'],
                'priority' => 20,
            ];
        }

        $config = [
            'http' => [
                'routers' => $routers,
                'services' => [
                    $project => [
                        'loadBalancer' => [
                            'servers' => [
                                ['url' => $containerUrl],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $yaml = $this->toYaml($config);
        file_put_contents("{$this->configPath}/tenant-{$subdomain}.yml", $yaml);
    }

    public function removeTenantConfig(Tenant $tenant): void
    {
        $file = "{$this->configPath}/tenant-{$tenant->subdomain}.yml";
        if (file_exists($file)) {
            unlink($file);
        }
    }

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
                $domain->update(['verified_at' => now(), 'ssl_provisioned' => true]);

                $tenant = $domain->tenant;
                $tenant->update(['custom_domain' => $domain->domain]);

                $this->writeTenantConfig($tenant);

                ActivityLog::log('domain.verified', "Domínio {$domain->domain} verificado e ativo", $domain->tenant_id);
                return true;
            }
        }

        return false;
    }

    public function removeDomain(Domain $domain): void
    {
        $tenant = $domain->tenant;

        if ($tenant->custom_domain === $domain->domain) {
            $nextDomain = $tenant->domains()
                ->where('id', '!=', $domain->id)
                ->whereNotNull('verified_at')
                ->first();
            $tenant->update(['custom_domain' => $nextDomain?->domain]);
        }

        $domain->delete();
        $this->writeTenantConfig($tenant);

        ActivityLog::log('domain.removed', "Domínio {$domain->domain} removido", $tenant->id);
    }

    public function getVerificationInstructions(Domain $domain): array
    {
        $baseDomain = config('master.base_domain');

        return [
            'txt' => [
                'type' => 'TXT',
                'host' => "_dinofy-verify.{$domain->domain}",
                'value' => $domain->verification_token,
            ],
            'cname' => [
                'type' => 'CNAME / A',
                'host' => $domain->domain,
                'value' => "{$domain->tenant->subdomain}.{$baseDomain}",
                'note' => 'Ou aponte um registro A para o IP do servidor.',
            ],
        ];
    }

    private function toYaml(array $data, int $indent = 0): string
    {
        $yaml = '';
        $pad = str_repeat('  ', $indent);

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (array_is_list($value)) {
                    $yaml .= "{$pad}{$key}:\n";
                    foreach ($value as $item) {
                        if (is_array($item)) {
                            $first = true;
                            foreach ($item as $k => $v) {
                                if ($first) {
                                    $yaml .= "{$pad}  - {$k}: \"{$v}\"\n";
                                    $first = false;
                                } else {
                                    $yaml .= "{$pad}    {$k}: \"{$v}\"\n";
                                }
                            }
                        } else {
                            $yaml .= "{$pad}  - {$item}\n";
                        }
                    }
                } else {
                    $yaml .= "{$pad}{$key}:\n";
                    $yaml .= $this->toYaml($value, $indent + 1);
                }
            } else {
                $yaml .= "{$pad}{$key}: \"{$value}\"\n";
            }
        }

        return $yaml;
    }
}
