# Test HTTP reel des API mobiles SIM + Comptable (local uniquement).
# Lit les identifiants dans le fichier prive (~/.csar/mobile-test-accounts.json).
# N'affiche JAMAIS les mots de passe ni les jetons.
# Usage : powershell -File scripts\test-mobile-api.ps1 [-BaseUrl http://192.168.2.109:8000]

param([string]$BaseUrl = "http://127.0.0.1:8000")

$ErrorActionPreference = 'Stop'
$credFile = Join-Path $env:USERPROFILE '.csar\mobile-test-accounts.json'
if (-not (Test-Path $credFile)) { Write-Error "Fichier d'identifiants introuvable: $credFile. Lancez: php artisan mobile:test-accounts" }
$cred = Get-Content $credFile -Raw | ConvertFrom-Json

$pass = 0; $fail = 0
function Check([string]$name, [bool]$ok, [string]$detail = '') {
    if ($ok) { $script:pass++; Write-Host "  PASS  $name" -ForegroundColor Green }
    else { $script:fail++; Write-Host "  FAIL  $name  $detail" -ForegroundColor Red }
}
function Req([string]$method, [string]$path, $body = $null, [string]$token = $null) {
    $h = @{ 'Accept' = 'application/json' }
    if ($token) { $h['Authorization'] = "Bearer $token" }
    $params = @{ Uri = "$BaseUrl$path"; Method = $method; Headers = $h; UseBasicParsing = $true; TimeoutSec = 15 }
    if ($body) { $params['Body'] = ($body | ConvertTo-Json); $params['ContentType'] = 'application/json' }
    try { return Invoke-RestMethod @params } catch { return $_.Exception.Response }
}
function StatusOf($r) { if ($r -is [System.Net.HttpWebResponse]) { [int]$r.StatusCode } elseif ($r -and $r.PSObject.Properties['success']) { 200 } else { 0 } }
function RawStatus([string]$method, [string]$path, $body = $null, [string]$token = $null) {
    $h = @{ 'Accept' = 'application/json' }
    if ($token) { $h['Authorization'] = "Bearer $token" }
    try {
        $params = @{ Uri = "$BaseUrl$path"; Method = $method; Headers = $h; UseBasicParsing = $true; TimeoutSec = 15 }
        if ($body) { $params['Body'] = ($body | ConvertTo-Json); $params['ContentType'] = 'application/json' }
        $r = Invoke-WebRequest @params
        return [int]$r.StatusCode
    } catch { if ($_.Exception.Response) { return [int]$_.Exception.Response.StatusCode } return 0 }
}

Write-Host "`n=== API Collecte SIM ($BaseUrl/mobile/v1) ===" -ForegroundColor Cyan
$login = Req 'POST' '/mobile/v1/login' @{ email = $cred.sim.email; password = $cred.sim.password }
$simToken = $login.data.token
Check 'login 200 + token recu' ($login.success -eq $true -and $simToken)
Check 'reponse sans password_hash' (-not ($login.data.collector.PSObject.Properties.Name -contains 'password_hash'))

$badStatus = RawStatus 'POST' '/mobile/v1/login' @{ email = $cred.sim.email; password = 'FAUX-motdepasse' }
Check 'mauvais mot de passe refuse' ($badStatus -eq 401) ("code=$badStatus")

$markets = Req 'GET' '/mobile/v1/markets' -token $simToken
$assignedId = [int]$cred.sim.assigned_market.id; $otherId = [int]$cred.sim.unassigned_market_for_negative_test.id
$marketIds = @($markets.data | ForEach-Object { [int]$_.id })
Check 'marches = uniquement marche assigne' (($marketIds -contains $assignedId) -and (-not ($marketIds -contains $otherId))) ("recus: $($marketIds -join ',')")

$product = (Req 'GET' '/mobile/v1/products' -token $simToken).data | Select-Object -First 1
$payload = @{ market_id = $assignedId; product_id = $product.id; retail_price = 450; collection_date = (Get-Date).ToString('yyyy-MM-dd') }
$col = Req 'POST' '/mobile/v1/collections' $payload $simToken
Check 'collecte sur marche assigne OK' ($col.success -eq $true)

$payload.market_id = $otherId
$colRef = Req 'POST' '/mobile/v1/collections' $payload $simToken
$refStatus = StatusOf $colRef
Check 'collecte sur marche NON assigne refusee' (($refStatus -eq 403) -or ($colRef.success -eq $false))

$hist = Req 'GET' '/mobile/v1/collections' -token $simToken
Check 'historique contient la collecte' ((@($hist.data).Count) -ge 1)

$sync = Req 'POST' '/mobile/v1/sync' $null $simToken
Check 'synchronisation pending -> synced' ($sync.success -eq $true -and $sync.data.synced_count -ge 1)

$syncHist = Req 'GET' '/mobile/v1/sync/history' -token $simToken
Check 'historique de sync alimente' ($syncHist.success -eq $true -and (@($syncHist.data.data).Count -ge 1))

Check 'sans token -> 401' ((RawStatus 'GET' '/mobile/v1/markets') -eq 401)

$out = Req 'POST' '/mobile/v1/logout' $null $simToken
Check 'logout revoque le token' ($out.success -eq $true)
Check 'token revoque refuse apres logout' ((RawStatus 'GET' '/mobile/v1/markets' $null $simToken) -eq 401)

Write-Host "`n=== API Comptable des matieres ($BaseUrl/api/comptable/v1) ===" -ForegroundColor Cyan
$clog = Req 'POST' '/api/comptable/v1/login' @{ email = $cred.comptable.email; password = $cred.comptable.password }
$ctok = $clog.token
Check 'login 200 + token recu' ($clog.user.role -eq 'comptable' -and $ctok)
Check 'reponse sans mot de passe' (-not ($clog.user.PSObject.Properties.Name -match 'password'))

foreach ($ep in 'dashboard','movements','warehouses','fuel/summary','equipment-assignments') {
    $r = Req 'GET' "/api/comptable/v1/$ep" -token $ctok
    Check "GET $ep -> 200" ($r -isnot [System.Net.HttpWebResponse])
}
Check 'sans token -> 401' ((RawStatus 'GET' '/api/comptable/v1/movements') -eq 401)

$cout = Req 'POST' '/api/comptable/v1/logout' $null $ctok
Check 'logout revoque le token' ($cout.message -match 'Déconnexion|Deconnexion')
Check 'token revoque refuse apres logout' ((RawStatus 'GET' '/api/comptable/v1/dashboard' $null $ctok) -eq 401)

Write-Host "`n=== Bilan : $pass reussis, $fail echoues ===" -ForegroundColor $(if ($fail) {'Red'} else {'Green'})
exit $fail
