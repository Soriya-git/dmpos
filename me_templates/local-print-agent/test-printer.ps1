param(
    [Parameter(Mandatory = $true)]
    [string]$PrinterIp,

    [int]$Port = 9100
)

Write-Host "Testing TCP connection to ${PrinterIp}:${Port}..."
$result = Test-NetConnection -ComputerName $PrinterIp -Port $Port

if (-not $result.TcpTestSucceeded) {
    Write-Error "Cannot reach the printer. Check its IP, port, power, and local network."
    exit 1
}

Write-Host "Success: the local computer can reach the printer." -ForegroundColor Green
