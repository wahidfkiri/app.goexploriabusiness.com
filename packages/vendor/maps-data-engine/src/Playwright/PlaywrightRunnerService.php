<?php

namespace Vendor\MapsDataEngine\Playwright;

use Illuminate\Process\ProcessResult;
use Illuminate\Support\Facades\Process;
use RuntimeException;

class PlaywrightRunnerService
{
    public function run(array $payload): array
    {
        $payloadDir = config('maps-data-engine.runtime.payload_directory');
        $resultDir = config('maps-data-engine.runtime.result_directory');

        if (! is_dir($payloadDir)) {
            mkdir($payloadDir, 0775, true);
        }

        if (! is_dir($resultDir)) {
            mkdir($resultDir, 0775, true);
        }

        $id = uniqid('mde_', true);
        $payloadPath = $payloadDir . DIRECTORY_SEPARATOR . $id . '.json';
        file_put_contents($payloadPath, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $command = [
            (string) config('maps-data-engine.runtime.node_binary', 'node'),
            (string) config('maps-data-engine.runtime.runner_script'),
            $payloadPath,
        ];

        $result = Process::path((string) config('maps-data-engine.runtime.working_directory'))
            ->timeout(600)
            ->run($command);

        return $this->normalizeResult($result, $payloadPath);
    }

    protected function normalizeResult(ProcessResult $result, string $payloadPath): array
    {
        if ($result->failed()) {
            throw new RuntimeException(trim($result->errorOutput() ?: $result->output() ?: 'Playwright worker failed.'));
        }

        $decoded = json_decode($result->output(), true);
        if (! is_array($decoded)) {
            throw new RuntimeException('Invalid JSON returned by Playwright worker.');
        }

        @unlink($payloadPath);

        return $decoded;
    }
}
