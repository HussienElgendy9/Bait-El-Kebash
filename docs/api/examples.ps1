param(
    [Parameter(Mandatory=$true)][string]$Email,
    [string]$BaseUrl = 'http://localhost:8000',
    [string]$Origin = 'http://localhost:5173',
    [switch]$PlaceOrder,
    [int]$ProductId = 1,
    [int]$Quantity = 1
)
$ErrorActionPreference = 'Stop'
$credential = Get-Credential -UserName $Email -Message 'Local API account'
$apiSession = New-Object Microsoft.PowerShell.Commands.WebRequestSession
function Invoke-Api([string]$Method, [string]$Path, $Body = $null, [string]$RetryKey = '') {
    $headers = @{ Origin = $Origin; Accept = 'application/json' }
    $xsrf = $apiSession.Cookies.GetCookies([uri]$BaseUrl)['XSRF-TOKEN']
    if ($xsrf) { $headers['X-XSRF-TOKEN'] = [uri]::UnescapeDataString($xsrf.Value) }
    if ($RetryKey) { $headers['Idempotency-Key'] = $RetryKey }
    $arguments = @{ Method=$Method; Uri=($BaseUrl.TrimEnd('/')+$Path); WebSession=$apiSession; Headers=$headers }
    if ($null -ne $Body) { $arguments.ContentType='application/json'; $arguments.Body=($Body | ConvertTo-Json -Depth 10 -Compress) }
    Invoke-RestMethod @arguments
}
Invoke-Api GET '/sanctum/csrf-cookie' | Out-Null
Invoke-Api POST '/api/v1/auth/login' @{ email=$Email; password=$credential.GetNetworkCredential().Password } | Out-Null
try {
    Invoke-Api GET '/api/v1/me' | ConvertTo-Json -Depth 10
    Invoke-Api GET '/api/v1/products?per_page=12' | ConvertTo-Json -Depth 10
    if ($PlaceOrder) {
        $retryKey = [guid]::NewGuid().ToString()
        Write-Output "Checkout Idempotency-Key: $retryKey"
        Invoke-Api POST '/api/v1/orders' @{ items=@(@{ product_id=$ProductId; quantity=$Quantity }) } $retryKey | ConvertTo-Json -Depth 10
    }
    Invoke-Api GET '/api/v1/orders' | ConvertTo-Json -Depth 10
} finally {
    Invoke-Api POST '/api/v1/auth/logout' | Out-Null
}

