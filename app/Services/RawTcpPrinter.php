<?php

namespace App\Services;

use App\Models\PrintJob;
use Throwable;

class RawTcpPrinter
{
    public function payload(PrintJob $printJob): string
    {
        $printJob->loadMissing('printer', 'branch');

        return $this->buildEscPosPayload($printJob);
    }

    public function print(PrintJob $printJob): bool
    {
        $printJob->loadMissing('printer', 'branch');

        $printer = $printJob->printer;

        if (! $printer || ! $printer->is_active) {
            return false;
        }

        if ($printer->connection_type !== 'network' || $printer->network_protocol !== 'raw_tcp') {
            return false;
        }

        $host = $printer->ip_address ?: $printer->host_name;

        if (! $host) {
            $this->markFailed($printJob, 'Printer host or IP address is missing.');

            return false;
        }

        $port = (int) ($printer->port ?: 9100);
        $timeout = max(1, (int) ceil(($printer->timeout_ms ?: 5000) / 1000));

        try {
            $socket = @fsockopen($host, $port, $errno, $errstr, $timeout);

            if (! $socket) {
                $this->markFailed($printJob, trim("{$errno} {$errstr}"));

                return false;
            }

            stream_set_timeout($socket, $timeout);
            fwrite($socket, $this->payload($printJob));
            fclose($socket);

            $printJob->forceFill([
                'status' => 'printed',
                'print_count' => $printJob->print_count + 1,
                'printed_at' => now(),
                'error_message' => null,
            ])->save();

            return true;
        } catch (Throwable $exception) {
            $this->markFailed($printJob, $exception->getMessage());

            return false;
        }
    }

    private function buildEscPosPayload(PrintJob $printJob): string
    {
        $payload = $printJob->payload ?? [];
        $lines = collect(data_get($payload, 'lines', []));
        $title = match ($printJob->job_type) {
            'stock_ticket' => 'STOCK SLIP',
            'bar_ticket' => 'BAR SLIP',
            'cancel_slip' => data_get($payload, 'is_return_slip') ? 'RETURN SLIP' : 'CANCEL SLIP',
            'receipt' => 'RECEIPT',
            default => 'KITCHEN SLIP',
        };

        $text = [];
        $text[] = $title;
        $text[] = str_repeat('-', 32);
        $text[] = 'No: '.$printJob->reference_no;
        $text[] = 'Job: '.$printJob->job_no;
        $text[] = 'Seat: '.(data_get($payload, 'order.seat') ?: '-');
        $text[] = 'Time: '.(data_get($payload, 'order.sent_at') ?: now()->format('Y-m-d H:i:s'));
        $text[] = 'Staff: '.(data_get($payload, 'order.created_by') ?: '-');
        $text[] = str_repeat('-', 32);

        foreach ($lines as $line) {
            $quantity = rtrim(rtrim(number_format((float) data_get($line, 'quantity', 0), 2), '0'), '.');
            $name = (string) data_get($line, 'name', 'Menu item');
            $status = data_get($line, 'status');

            $text[] = $quantity.' x '.$name.($status ? ' ('.strtoupper((string) $status).')' : '');

            if ($note = data_get($line, 'note')) {
                $text[] = '  * '.$note;
            }
        }

        $text[] = str_repeat('-', 32);
        $text[] = 'Printer: '.($printJob->printer?->name ?: 'Default Printer');
        $text[] = '';
        $text[] = '';
        $text[] = '';

        return "\x1B\x40"
            .$this->encodeText(implode("\n", $text)."\n")
            ."\x1D\x56\x41\x10";
    }

    private function encodeText(string $text): string
    {
        return mb_convert_encoding($text, 'UTF-8', 'UTF-8');
    }

    private function markFailed(PrintJob $printJob, string $message): void
    {
        $printJob->forceFill([
            'status' => 'failed',
            'error_message' => $message ?: 'Unable to print.',
        ])->save();
    }
}
