# DMPOS Local Print Agent

This folder is copied to one always-on Windows computer on the same LAN as the Xprinter. The agent securely claims queued tickets from the Laravel VPS and sends ESC/POS bytes to the printer over raw TCP, normally port 9100.

## Requirements

- Windows 10/11 or Windows Server
- PHP 8.1 or newer available in `PATH`
- PHP cURL extension enabled
- Computer and Xprinter on the same local network
- Xprinter configured with a fixed/reserved IP address

## VPS preparation

After deploying the matching Laravel code:

```bash
php artisan migrate --force
php artisan print-agent:token 1
```

Replace `1` with the branch ID. Save the displayed token immediately. Generating another token invalidates the old one.

## Local computer setup

1. Copy this entire `local-print-agent` folder to a permanent location such as `C:\DMPOS\local-print-agent`.
2. Edit `config.php` and set the real HTTPS domain and generated token.
3. Confirm PHP and cURL:

```powershell
php --version
php -m | Select-String curl
```

4. Test printer access, replacing the IP:

```powershell
powershell -ExecutionPolicy Bypass -File .\test-printer.ps1 -PrinterIp 192.168.1.50 -Port 9100
```

5. Double-click `start-agent.cmd`. Keep it open and press Confirm Order in DMPOS.
6. Check `print-agent.log` and the DMPOS printer logs.

## Automatic startup

After manual printing works, open PowerShell as Administrator in this folder and run:

```powershell
powershell -ExecutionPolicy Bypass -File .\install-startup-task.ps1
```

This installs `DMPOS Local Print Agent` as a Windows startup task running under SYSTEM. The computer must remain powered on and connected to the restaurant network.

## Printer configuration in DMPOS

- Connection type: `network`
- Protocol: `raw_tcp`
- IP address: the Xprinter fixed LAN IP
- Port: normally `9100`
- Active: enabled
- Role/default: appropriate for kitchen, stock, bar, or cashier

Menus must have the matching print route. Never expose printer port 9100 through internet router port forwarding.

## Troubleshooting

- `401 Unauthorized`: generate a new branch token and update `config.php`.
- `VPS connection failed`: verify the HTTPS URL, internet access, certificate, and token.
- `Cannot reach printer`: verify the printer IP/port and ensure Wi-Fi client isolation is disabled.
- Job remains pending: verify the agent is running and uses the token for the same branch as the job.
- Job is failed: review `print-agent.log` and DMPOS Access Control > Printer Logs, then use Reprint after correcting the issue.
