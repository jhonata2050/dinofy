<?php

namespace App\Services;

use App\Models\Tenant;

class DockerComposeGenerator
{
    public function generate(Tenant $tenant): string
    {
        $plan = $tenant->plan;
        $baseDomain = config('master.base_domain');
        $image = config('master.dinofy_image');

        $replacements = [
            '{{DINOFY_IMAGE}}' => $image,
            '{{CPU_LIMIT}}' => (string) $plan->cpu_limit,
            '{{MEMORY_LIMIT}}' => $plan->memory_limit,
            '{{CPU_RESERVATION}}' => $plan->cpuReservation(),
            '{{MEMORY_RESERVATION}}' => $plan->memoryReservation(),
            '{{SUBDOMAIN}}' => $tenant->subdomain,
            '{{BASE_DOMAIN}}' => $baseDomain,
            '{{PROJECT_NAME}}' => $tenant->projectName(),
            '{{TENANT_ID}}' => (string) $tenant->id,
            '{{DB_DATABASE}}' => 'dinofy_' . $tenant->subdomain,
            '{{DB_USERNAME}}' => 'user_' . $tenant->subdomain,
            '{{DB_PASSWORD}}' => $tenant->db_password,
            '{{DB_ROOT_PASSWORD}}' => $tenant->db_password . '_root',
            '{{APP_KEY}}' => $tenant->app_key,
            '{{MAX_DB_CONNECTIONS}}' => (string) $plan->max_db_connections,
        ];

        $stub = file_get_contents(base_path('stubs/tenant-docker-compose.yml'));

        return str_replace(array_keys($replacements), array_values($replacements), $stub);
    }

    public function saveToDisk(Tenant $tenant): string
    {
        $content = $this->generate($tenant);
        $path = $tenant->data_path . '/docker-compose.yml';

        if (!is_dir($tenant->data_path)) {
            mkdir($tenant->data_path, 0755, true);
        }

        file_put_contents($path, $content);

        return $path;
    }
}
