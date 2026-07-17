$files = Get-ChildItem -Filter *.html -Recurse

# Unicode representation for "色彩鑑定" and "線上試妝"
$colorIdText = "$([char]0x8272)$([char]0x5F69)$([char]0x9451)$([char]0x5B9A)"
$tryonText = "$([char]0x7D9A)$([char]0x4E0A)$([char]0x8A96)$([char]0x599D)"

foreach ($file in $files) {
    if ($file.Name -eq "makeup-tryon.html") { continue }
    
    $c = [System.IO.File]::ReadAllText($file.FullName, [System.Text.Encoding]::UTF8)
    
    $c = $c -replace 'href="css/style\.css(\?v=[0-9]+)?"', 'href="css/style.css?v=6"'
    
    $c = $c.Replace("AI $colorIdText", $colorIdText)
    
    $c = $c.Replace('"login.html"', '"newlogin.html"')
    $c = $c.Replace('"account.html"', '"newaccount.html"')
    
    if ($c.IndexOf('makeup-tryon.html') -eq -1) {
        $c = $c -replace '(<li><a href="destiny-color\.html">[^<]*</a></li>)', "`$1`n      <li><a href=`"makeup-tryon.html`">$tryonText</a></li>"
        $c = $c -replace '(<a href="destiny-color\.html">[^<]*</a>)', "`$1`n        <a href=`"makeup-tryon.html`">$tryonText</a>"
        $c = $c -replace '(<a href="destiny-color\.html" class="active">[^<]*</a>)', "`$1`n        <a href=`"makeup-tryon.html`">$tryonText</a>"
    }

    [System.IO.File]::WriteAllText($file.FullName, $c, [System.Text.Encoding]::UTF8)
}

if (Test-Path "login.html") { Remove-Item "login.html" -Force }
if (Test-Path "account.html") { Remove-Item "account.html" -Force }
