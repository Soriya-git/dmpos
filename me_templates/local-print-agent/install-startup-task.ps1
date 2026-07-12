$ErrorActionPreference = 'Stop'
$agentDirectory = Split-Path -Parent $MyInvocation.MyCommand.Path
$php = (Get-Command php -ErrorAction Stop).Source
$agent = Join-Path $agentDirectory 'print-agent.php'
$taskName = 'DMPOS Local Print Agent'

$action = New-ScheduledTaskAction -Execute $php -Argument ('"{0}"' -f $agent) -WorkingDirectory $agentDirectory
$trigger = New-ScheduledTaskTrigger -AtStartup
$settings = New-ScheduledTaskSettingsSet -RestartCount 999 -RestartInterval (New-TimeSpan -Minutes 1) -StartWhenAvailable
$principal = New-ScheduledTaskPrincipal -UserId 'SYSTEM' -LogonType ServiceAccount -RunLevel Highest

Register-ScheduledTask -TaskName $taskName -Action $action -Trigger $trigger -Settings $settings -Principal $principal -Force | Out-Null
Start-ScheduledTask -TaskName $taskName

Write-Host "Installed and started: $taskName" -ForegroundColor Green
Write-Host "Log file: $(Join-Path $agentDirectory 'print-agent.log')"
