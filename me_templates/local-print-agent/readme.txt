Created the complete local print-agent package here:
[local-print-agent](D:/Lar/dmpos/me_templates/local-print-agent)
Included files:
print-agent.php — background printing agent
config.php — VPS URL and branch token configuration
start-agent.cmd — manual Windows startup
test-printer.ps1 — tests printer port 9100
install-startup-task.ps1 — installs automatic Windows startup
README.md — complete setup and troubleshooting guide
Before copying, deploy Laravel and run:
php artisan migrate --force
php artisan print-agent:token 1
Then on the local computer:
Copy the entire local-print-agent folder.
Edit config.php.
Enter your VPS domain and generated token.
Test the printer:

powershell -ExecutionPolicy Bypass -File .\test-printer.ps1 -PrinterIp 192.168.1.50

Double-click start-agent.cmd.
Test Confirm Order.
Once successful, install automatic startup from Administrator PowerShell:

powershell -ExecutionPolicy Bypass -File .\install-startup-task.ps1

PHP syntax, PowerShell syntax, placeholder protection, and diff formatting all passed.