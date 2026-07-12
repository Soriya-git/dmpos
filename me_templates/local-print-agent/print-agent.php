<?php

declare(strict_types=1);

$config = require __DIR__.'/config.php';

foreach (['api_base', 'api_token', 'poll_seconds', 'request_timeout_seconds', 'claim_limit', 'log_file'] as $key) {
    if (! array_key_exists($key, $config)) {
        fwrite(STDERR, "Missing config value: {$key}".PHP_EOL);
        exit(1);
    }
}

if (! extension_loaded('curl')) {
    fwrite(STDERR, 'PHP cURL extension is required.'.PHP_EOL);
    exit(1);
}

if (str_contains((string) $config['api_base'], 'YOUR-DOMAIN') || str_contains((string) $config['api_token'], 'PASTE_')) {
    fwrite(STDERR, 'Edit config.php before starting the print agent.'.PHP_EOL);
    exit(1);
}

function logMessage(array $config, string $message): void
{
    $line = '['.date('Y-m-d H:i:s').'] '.$message.PHP_EOL;
    echo $line;
    file_put_contents((string) $config['log_file'], $line, FILE_APPEND | LOCK_EX);
}

/** @return array{status: int, body: array<string, mixed>|null, error: string|null} */
function apiPost(array $config, string $path, array $data): array
{
    $handle = curl_init(rtrim((string) $config['api_base'], '/').$path);
    curl_setopt_array($handle, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data, JSON_THROW_ON_ERROR),
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => (int) $config['request_timeout_seconds'],
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer '.$config['api_token'],
            'Accept: application/json',
            'Content-Type: application/json',
            'User-Agent: DMPOS-Local-Print-Agent/1.0',
        ],
    ]);

    $response = curl_exec($handle);
    $error = curl_error($handle) ?: null;
    $status = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
    curl_close($handle);

    $body = is_string($response) && $response !== '' ? json_decode($response, true) : null;

    return ['status' => $status, 'body' => is_array($body) ? $body : null, 'error' => $error];
}

function printRaw(string $host, int $port, string $payload, int $timeoutMs): ?string
{
    $timeout = max(1, (int) ceil($timeoutMs / 1000));
    $socket = @fsockopen($host, $port, $errorNumber, $errorMessage, $timeout);

    if (! is_resource($socket)) {
        return trim("Socket {$errorNumber}: {$errorMessage}");
    }

    stream_set_timeout($socket, $timeout);
    $length = strlen($payload);
    $written = 0;

    while ($written < $length) {
        $bytes = fwrite($socket, substr($payload, $written));

        if ($bytes === false || $bytes === 0) {
            fclose($socket);

            return 'Printer socket closed before the complete ticket was sent.';
        }

        $written += $bytes;
    }

    fflush($socket);
    $metadata = stream_get_meta_data($socket);
    fclose($socket);

    return ($metadata['timed_out'] ?? false) ? 'Printer socket timed out.' : null;
}

logMessage($config, 'DMPOS local print agent started.');

while (true) {
    try {
        $claim = apiPost($config, '/claim', ['limit' => (int) $config['claim_limit']]);

        if ($claim['status'] !== 200) {
            $detail = $claim['error'] ?: ($claim['body']['message'] ?? 'HTTP '.$claim['status']);
            logMessage($config, 'VPS connection failed: '.$detail);
            sleep((int) $config['poll_seconds']);
            continue;
        }

        $jobs = $claim['body']['data'] ?? [];

        foreach ($jobs as $job) {
            $id = (int) ($job['id'] ?? 0);
            $jobNo = (string) ($job['job_no'] ?? $id);
            $claimToken = (string) ($job['claim_token'] ?? '');
            $host = (string) ($job['printer_ip'] ?? '');
            $port = (int) ($job['printer_port'] ?? 9100);
            $timeoutMs = (int) ($job['timeout_ms'] ?? 5000);
            $payload = base64_decode((string) ($job['payload_b64'] ?? ''), true);

            logMessage($config, "Printing {$jobNo} to {$host}:{$port}");

            $failure = $host === '' ? 'Printer IP or host is missing.' : null;
            $failure ??= $payload === false || $payload === '' ? 'The print payload is empty or invalid.' : null;
            $failure ??= printRaw($host, $port, (string) $payload, $timeoutMs);

            if ($failure === null) {
                $ack = apiPost($config, "/jobs/{$id}/printed", ['claim_token' => $claimToken]);
                logMessage($config, $ack['status'] === 200 ? "Printed {$jobNo}" : "Printed {$jobNo}, but VPS acknowledgement failed (HTTP {$ack['status']}).");
                continue;
            }

            apiPost($config, "/jobs/{$id}/failed", [
                'claim_token' => $claimToken,
                'error' => $failure,
            ]);
            logMessage($config, "Failed {$jobNo}: {$failure}");
        }
    } catch (Throwable $exception) {
        logMessage($config, 'Unexpected agent error: '.$exception->getMessage());
    }

    sleep((int) $config['poll_seconds']);
}
