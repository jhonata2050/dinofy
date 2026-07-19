<?php

namespace App\Services;

use App\Models\Tenant;
use Symfony\Component\Process\Process;

class DockerManager
{
    public function up(Tenant $tenant): array
    {
        return $this->compose($tenant, ['up', '-d', '--remove-orphans']);
    }

    public function down(Tenant $tenant): array
    {
        return $this->compose($tenant, ['down']);
    }

    public function stop(Tenant $tenant): array
    {
        return $this->compose($tenant, ['stop']);
    }

    public function start(Tenant $tenant): array
    {
        return $this->compose($tenant, ['start']);
    }

    public function exec(Tenant $tenant, string $service, array $command): array
    {
        return $this->compose($tenant, array_merge(['exec', '-T', $service], $command));
    }

    public function destroy(Tenant $tenant): array
    {
        return $this->compose($tenant, ['down', '-v', '--remove-orphans']);
    }

    public function isRunning(Tenant $tenant): bool
    {
        $result = $this->compose($tenant, ['ps', '-q', '--filter', 'status=running']);

        if ($result['success'] && !empty(trim($result['output']))) {
            return true;
        }

        $subdomain = $tenant->subdomain;
        $project = $tenant->projectName();

        $process = new Process(['docker', 'ps', '-q', '--filter', "name={$subdomain}-", '--filter', "status=running"]);
        $process->run();
        if ($process->isSuccessful() && !empty(trim($process->getOutput()))) {
            return true;
        }

        $process2 = new Process(['docker', 'ps', '-q', '--filter', "name={$project}-", '--filter', "status=running"]);
        $process2->run();
        return $process2->isSuccessful() && !empty(trim($process2->getOutput()));
    }

    public function stats(Tenant $tenant): array
    {
        $project = $tenant->projectName();
        $subdomain = $tenant->subdomain;

        $process = new Process([
            'docker', 'stats', '--no-stream', '--format',
            '{"name":"{{.Name}}","cpu":"{{.CPUPerc}}","memory":"{{.MemUsage}}","mem_percent":"{{.MemPerc}}"}',
        ]);
        $process->setTimeout(15);
        $process->run();

        if (!$process->isSuccessful()) {
            return [];
        }

        $stats = [];
        foreach (explode("\n", trim($process->getOutput())) as $line) {
            $data = json_decode($line, true);
            $name = $data['name'] ?? '';
            if ($data && (str_starts_with($name, $project) || str_starts_with($name, $subdomain . '-'))) {
                $stats[] = $data;
            }
        }

        return $stats;
    }

    public function logs(Tenant $tenant, int $lines = 50): string
    {
        $result = $this->compose($tenant, ['logs', '--tail', (string) $lines, '--no-color']);
        return $result['output'] ?? '';
    }

    private function compose(Tenant $tenant, array $args): array
    {
        $composePath = $tenant->data_path . '/docker-compose.yml';

        if (!file_exists($composePath) && !in_array('down', $args)) {
            return ['success' => false, 'output' => 'Compose file not found', 'exit_code' => 1];
        }

        $cmd = array_merge(
            ['docker', 'compose', '-p', $tenant->projectName(), '-f', $composePath],
            $args
        );

        $process = new Process($cmd);
        $process->setTimeout(120);
        $process->run();

        return [
            'success' => $process->isSuccessful(),
            'output' => $process->getOutput() . $process->getErrorOutput(),
            'exit_code' => $process->getExitCode(),
        ];
    }
}
