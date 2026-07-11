<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Tenant;
use Symfony\Component\Process\Process;

class TenantUpdater
{
    public function __construct(
        private readonly DockerComposeGenerator $generator,
        private readonly DockerManager $docker,
    ) {}

    public function pullImage(): array
    {
        $image = config('master.dinofy_image');
        $process = new Process(['docker', 'pull', $image]);
        $process->setTimeout(300);
        $process->run();

        return [
            'success' => $process->isSuccessful(),
            'output' => $process->getOutput() . $process->getErrorOutput(),
            'image' => $image,
        ];
    }

    public function updateAll(): array
    {
        $tenants = Tenant::where('status', 'active')->get();
        $results = [];

        ActivityLog::log('platform.update_started', "Atualizando {$tenants->count()} tenants");

        foreach ($tenants as $tenant) {
            $results[$tenant->subdomain] = $this->updateTenant($tenant);
        }

        $success = collect($results)->where('success', true)->count();
        $failed = collect($results)->where('success', false)->count();

        ActivityLog::log('platform.update_completed', "Update concluído: {$success} ok, {$failed} falhas");

        return [
            'total' => $tenants->count(),
            'success' => $success,
            'failed' => $failed,
            'details' => $results,
        ];
    }

    public function updateTenant(Tenant $tenant): array
    {
        try {
            $this->generator->saveToDisk($tenant);

            $result = $this->docker->up($tenant);

            if (!$result['success']) {
                ActivityLog::log('tenant.update_failed', "Falha ao atualizar: {$result['output']}", $tenant->id);
                return ['success' => false, 'error' => $result['output']];
            }

            $healthy = $this->waitForHealthy($tenant, 60);

            if (!$healthy) {
                ActivityLog::log('tenant.update_unhealthy', "Tenant não ficou healthy após update", $tenant->id);
                return ['success' => false, 'error' => 'Health check failed after update'];
            }

            ActivityLog::log('tenant.updated', "Tenant atualizado com sucesso", $tenant->id);
            return ['success' => true];
        } catch (\Exception $e) {
            ActivityLog::log('tenant.update_failed', "Erro: {$e->getMessage()}", $tenant->id);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function runMigrations(Tenant $tenant): array
    {
        $project = $tenant->projectName();
        $process = new Process([
            'docker', 'compose', '-p', $project,
            '-f', $tenant->data_path . '/docker-compose.yml',
            'exec', '-T', 'app',
            'php', 'artisan', 'migrate', '--force', '--no-interaction',
        ]);
        $process->setTimeout(120);
        $process->run();

        return [
            'success' => $process->isSuccessful(),
            'output' => $process->getOutput() . $process->getErrorOutput(),
        ];
    }

    private function waitForHealthy(Tenant $tenant, int $timeoutSeconds): bool
    {
        $start = time();

        while (time() - $start < $timeoutSeconds) {
            if ($this->docker->isRunning($tenant)) {
                return true;
            }
            sleep(3);
        }

        return false;
    }
}
