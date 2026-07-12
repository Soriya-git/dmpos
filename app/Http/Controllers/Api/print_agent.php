<?php

/**
 * DMPOS Local Print Agent
 * -----------------------
 * Run this script on any PC/server that is on the SAME local network as your printers.
 * It polls the VPS for pending print jobs and sends ESC/POS bytes directly to the printer.
 *
 * Requirements: PHP 8.1+ with curl extension
 *
 * Usage:
 *   php print_agent.php
 *
 * Or run as a background service on Windows:
 *   start /B php print_agent.php > print_agent.log 2>&1
 *
 * On Linux/Mac:
 *   nohup php print_agent.php > print_agent.log 2>&1 &
 */

// ─────────────────────────────────────────────
// CONFIG — edit these values
// ─────────────────────────────────────────────
define('API_BASE', 'https://dmpos.industrialefficiency.online/api/print-agent');
define('API_TOKEN', 'YOUR_PRINT_AGENT_TOKEN_HERE');   // from branches.print_agent_token
define('BRANCH_ID', 1);                                // your branch ID
define('POLL_SECS', 3);                                // how often to poll (seconds)
define('LOG_FILE', __DIR__.'/print_agent.log');
// ─────────────────────────────────────────────

function logLine(string $msg): void
{
    $line = '['.date('Y-m-d H:i:s').'] '.$msg.PHP_EOL;
    echo $line;
    file_put_contents(LOG_FILE, $line, FILE_APPEND);
}

function apiGet(string $url): ?array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer '.API_TOKEN,
            'Accept: application/json',
        ],
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code !== 200 || ! $body) {
        return null;
    }

    return json_decode($body, true);
}

function apiPost(string $url, array $data = []): array|false
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer '.API_TOKEN,
            'Accept: application/json',
            'Content-Type: application/json',
        ],
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code !== 200 || ! $body) {
        return false;
    }

    return json_decode($body, true) ?: [];
}

function sendToPrinter(string $ip, int $port, string $payload, int $timeoutMs): bool
{
    $timeout = max(1, (int) ceil($timeoutMs / 1000));
    $socket = @fsockopen($ip, $port, $errno, $errstr, $timeout);

    if (! $socket) {
        logLine("  Socket error [{$errno}]: {$errstr}");

        return false;
    }

    stream_set_timeout($socket, $timeout);
    fwrite($socket, $payload);
    fclose($socket);

    return true;
}

// ─────────────────────────────────────────────
// MAIN LOOP
// ─────────────────────────────────────────────
logLine('Print agent started. Polling every '.POLL_SECS.' seconds...');

while (true) {
    $response = apiPost(API_BASE.'/claim', ['limit' => 10]);

    if ($response === null) {
        logLine('WARNING: Could not reach API, will retry...');
        sleep(POLL_SECS);

        continue;
    }

    $jobs = $response['data'] ?? [];

    if (empty($jobs)) {
        sleep(POLL_SECS);

        continue;
    }

    logLine('Found '.count($jobs).' pending job(s).');

    foreach ($jobs as $job) {
        $id = $job['id'];
        $no = $job['job_no'];
        $ip = $job['printer_ip'];
        $port = (int) ($job['printer_port'] ?? 9100);
        $ms = (int) ($job['timeout_ms'] ?? 5000);
        $claimToken = $job['claim_token'] ?? '';
        $data = base64_decode($job['payload_b64'] ?? '');

        logLine("  Job #{$id} ({$no}) → {$ip}:{$port}");

        if (! $ip) {
            logLine('  SKIP: no printer IP.');
            apiPost(API_BASE.'/jobs/'.$id.'/failed', [
                'claim_token' => $claimToken,
                'error' => 'No printer IP configured.',
            ]);

            continue;
        }

        if (! $data) {
            logLine('  SKIP: empty payload.');
            apiPost(API_BASE.'/jobs/'.$id.'/failed', [
                'claim_token' => $claimToken,
                'error' => 'Empty payload.',
            ]);

            continue;
        }

        $ok = sendToPrinter($ip, $port, $data, $ms);

        if ($ok) {
            logLine('  SUCCESS');
            apiPost(API_BASE.'/jobs/'.$id.'/printed', ['claim_token' => $claimToken]);
        } else {
            logLine('  FAILED');
            apiPost(API_BASE.'/jobs/'.$id.'/failed', [
                'claim_token' => $claimToken,
                'error' => "Cannot connect to {$ip}:{$port}",
            ]);
        }
    }

    sleep(POLL_SECS);
}
