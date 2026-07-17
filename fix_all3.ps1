$files = Get-ChildItem -Filter *.html -Recurse
foreach ($file in $files) {
    $c = [System.IO.File]::ReadAllText($file.FullName, [System.Text.Encoding]::UTF8)
    $c = $c -replace 'href="css/style\.css(\?v=[0-9]+)?"', 'href="css/style.css?v=6"'
    $c = $c.Replace('AI 色彩鑑定', '色彩鑑定')
    $c = $c.Replace('"login.html"', '"newlogin.html"')
    $c = $c.Replace('"account.html"', '"newaccount.html"')
    [System.IO.File]::WriteAllText($file.FullName, $c, [System.Text.Encoding]::UTF8)
}
if (Test-Path "login.html") { Remove-Item "login.html" -Force }
if (Test-Path "account.html") { Remove-Item "account.html" -Force }
