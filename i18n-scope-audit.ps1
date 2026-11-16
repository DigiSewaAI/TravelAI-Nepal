# i18n-scope-audit.ps1 - i18n verification for public + traveler views only

$ErrorActionPreference = 'Stop'
$OutputEncoding = [System.Text.Encoding]::UTF8

$report = @()
$report += "# i18n Scope Audit - Public + Traveler"
$report += "Generated: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"
$report += ""

# Scope: ONLY public + traveler + their 2 layouts
$scopeFiles = @()
if (Test-Path 'resources\views\public') {
    $scopeFiles += Get-ChildItem -Path 'resources\views\public' -Recurse -Filter '*.blade.php'
}
if (Test-Path 'resources\views\traveler') {
    $scopeFiles += Get-ChildItem -Path 'resources\views\traveler' -Recurse -Filter '*.blade.php'
}
if (Test-Path 'resources\views\layouts\public.blade.php') {
    $scopeFiles += Get-Item 'resources\views\layouts\public.blade.php'
}
if (Test-Path 'resources\views\layouts\traveler-v2.blade.php') {
    $scopeFiles += Get-Item 'resources\views\layouts\traveler-v2.blade.php'
}

$report += "**Scope:** $($scopeFiles.Count) files (public + traveler + 2 layouts)"
$report += "**Excluded:** provider/, admin/, emails/, provider-* layouts (English-only by design)"
$report += ""

# Load all locale keys
$locales = @('en', 'np', 'hi', 'zh', 'ru')
$localeKeys = @{}
foreach ($loc in $locales) {
    $path = "resources\lang\$loc\messages.php"
    if (Test-Path $path) {
        $content = Get-Content $path -Raw -Encoding UTF8
        $matches = [regex]::Matches($content, "['""]([a-zA-Z0-9_]+)['""]\s*=>")
        $keys = @{}
        foreach ($m in $matches) { $keys[$m.Groups[1].Value] = $true }
        $localeKeys[$loc] = $keys
    }
}

# Per-file scorecard
$report += "## Per-Page Scorecard"
$report += ""
$report += "| View File | Keys | en miss | np miss | hi miss | zh miss | ru miss |"
$report += "|-----------|------|---------|---------|---------|---------|---------|"

$pageDetails = @{}

foreach ($v in $scopeFiles) {
    $content = Get-Content $v.FullName -Raw -Encoding UTF8
    $keyMatches = [regex]::Matches($content, "__\(\s*['""]messages\.([a-zA-Z0-9_]+)['""]")
    $fileKeys = @{}
    foreach ($m in $keyMatches) { $fileKeys[$m.Groups[1].Value] = $true }
    
    if ($fileKeys.Count -eq 0) { continue }
    
    $rel = $v.FullName.Replace((Get-Location).Path + '\', '')
    $pageDetails[$rel] = @{ missing = @{}; keys = $fileKeys.Keys }
    
    $counts = @{}
    foreach ($loc in $locales) {
        $missingHere = @()
        foreach ($key in $fileKeys.Keys) {
            if (-not $localeKeys[$loc].ContainsKey($key)) {
                $missingHere += $key
            }
        }
        $pageDetails[$rel].missing[$loc] = $missingHere
        $counts[$loc] = $missingHere.Count
    }
    
    $report += "| $rel | $($fileKeys.Count) | $($counts['en']) | $($counts['np']) | $($counts['hi']) | $($counts['zh']) | $($counts['ru']) |"
}
$report += ""

# Summary totals
$report += "## Summary by Locale (public + traveler only)"
$report += ""
$totals = @{}
foreach ($loc in $locales) {
    $allMissing = @{}
    foreach ($rel in $pageDetails.Keys) {
        foreach ($k in $pageDetails[$rel].missing[$loc]) { $allMissing[$k] = $true }
    }
    $totals[$loc] = $allMissing.Count
    $report += "- **$loc**: $($allMissing.Count) unique keys missing across scope"
}
$report += ""

# Detailed per-file missing (only files with issues)
$report += "## Detailed Missing Keys (only files with gaps in np/hi/zh/ru)"
$report += ""

foreach ($rel in ($pageDetails.Keys | Sort-Object)) {
    $hasMissing = $false
    foreach ($loc in @('np', 'hi', 'zh', 'ru')) {
        if ($pageDetails[$rel].missing[$loc].Count -gt 0) { $hasMissing = $true; break }
    }
    if (-not $hasMissing) { continue }
    
    $report += "### $rel"
    foreach ($loc in @('np', 'hi', 'zh', 'ru')) {
        $m = $pageDetails[$rel].missing[$loc]
        if ($m.Count -gt 0) {
            $list = ($m | Sort-Object | Select-Object -First 20) -join ', '
            if ($m.Count -gt 20) { $list += " ... +$($m.Count - 20) more" }
            $report += "- **$loc** ($($m.Count)): $list"
        }
    }
    $report += ""
}

# Hardcoded text (never translated)
$report += "## Hardcoded Text (never wrapped in __())"
$report += ""
$report += "Note: heuristic. Proper nouns/brand names OK to skip."
$report += ""

foreach ($v in $scopeFiles) {
    $rel = $v.FullName.Replace((Get-Location).Path + '\', '')
    $lines = Get-Content $v.FullName -Encoding UTF8
    $findings = @()
    for ($i = 0; $i -lt $lines.Count; $i++) {
        $line = $lines[$i]
        if ($line -match '__\(' -or $line -match '^\s*<!--' -or $line -match '^\s*\{\{' -or $line -match '@' -or $line -match '^\s*//' -or $line -match '^\s*\*') { continue }
        if ($line.Length -gt 400) { continue }
        if ($line -match '>[A-Za-z][A-Za-z0-9 ,''.?!:;()-]{4,}<') {
            $findings += "  - line $($i+1): $($line.Trim())"
        }
    }
    if ($findings.Count -gt 0) {
        $report += "### $rel - $($findings.Count) potential"
        $report += ($findings | Select-Object -First 15)
        if ($findings.Count -gt 15) { $report += "  - ... and $($findings.Count - 15) more" }
        $report += ""
    }
}

# Save
$reportPath = 'docs\i18n-scope-report.md'
if (-not (Test-Path 'docs')) { New-Item -ItemType Directory -Path 'docs' | Out-Null }
$report -join "`r`n" | Out-File -FilePath $reportPath -Encoding UTF8

Write-Host ""
Write-Host "[PASS] Report saved: $reportPath" -ForegroundColor Green
Write-Host ""
Write-Host "Quick Summary:" -ForegroundColor Cyan
Write-Host "  Scope files: $($scopeFiles.Count)"
foreach ($loc in $locales) {
    Write-Host "  Missing in ${loc}: $($totals[$loc]) unique keys"
}