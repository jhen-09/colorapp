$files = Get-ChildItem -Filter *.html -Recurse

# Unicode representation for "色彩鑑定" and "線上試妝"
$colorIdText = "$([char]0x8272)$([char]0x5F69)$([char]0x9451)$([char]0x5B9A)"
$tryonText = "$([char]0x7D9A)$([char]0x4E0A)$([char]0x8A96)$([char]0x599D)"

foreach ($file in $files) {
    if ($file.Name -eq "makeup-tryon.html") { continue }
    
    $c = [System.IO.File]::ReadAllText($file.FullName, [System.Text.Encoding]::UTF8)
    
    # Update CSS version
    $c = $c -replace 'href="css/style.css\??[a-zA-Z0-9=]*"', 'href="css/style.css?v=6"'
    
    # Remove AI
    $c = $c -replace "AI $colorIdText", $colorIdText
    
    # Update links
    $c = $c -replace 'href="login\.html"', 'href="newlogin.html"'
    $c = $c -replace 'href="account\.html"', 'href="newaccount.html"'
    $c = $c -replace 'action="account\.html"', 'action="newaccount.html"'
    
    # Insert 線上試妝 into nav if missing
    if ($c -notmatch 'makeup-tryon\.html') {
        $c = $c -replace '(<li><a href="destiny-color\.html">[^<]*</a></li>)', "`$1`n      <li><a href=`"makeup-tryon.html`">$tryonText</a></li>"
        $c = $c -replace '(<a href="destiny-color\.html">[^<]*</a>)', "`$1`n        <a href=`"makeup-tryon.html`">$tryonText</a>"
        $c = $c -replace '(<a href="destiny-color\.html" class="active">[^<]*</a>)', "`$1`n        <a href=`"makeup-tryon.html`">$tryonText</a>"
    }

    [System.IO.File]::WriteAllText($file.FullName, $c, [System.Text.Encoding]::UTF8)
}
