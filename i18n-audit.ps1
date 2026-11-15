# i18n-audit.ps1 - Audit script for TravelAI Nepal i18n
# Run: powershell -ExecutionPolicy Bypass -File i18n-audit.ps1

$ErrorActionPreference = 'Stop'
$OutputEncoding = [System.Text.Encoding]::UTF8
$report = @()
$report += "# i18n Audit Report - TravelAI Nepal"
$report += "Generated: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"
$report += ""

# ============================================================
# 1. VIEW FILES
# ============================================================
$viewDirs = @(
    'resources\views\public',
    'resources\views\traveler',
    'resources\views\emails',
    'resources\views\layouts'
)
$views = @()
foreach ($dir in $viewDirs) {
    if (Test-Path $dir) {
        $views += Get-ChildItem -Path $dir -Recurse -Filter '*.blade.php'
    }
}

$report += "## 1. View Files Audited ($($views.Count) total)"
$report += ""
foreach ($v in $views) {
    $rel = $v.FullName.Replace((Get-Location).Path + '\', '')
    $report += "- $rel"
}
$report += ""

# ============================================================
# 2. KEYS USED IN VIEWS
# ============================================================
$usedKeys = @{}
foreach ($v in $views) {
    $content = Get-Content $v.FullName -Raw -Encoding UTF8
    $matches = [regex]::Matches($content, '__\(\s*[''""]messages\.([a-zA-Z0-9_]+)[''""]')
    foreach ($m in $matches) {
        $key = $m.Groups[1].Value
        if (-not $usedKeys.ContainsKey($key)) { $usedKeys[$key] = @() }
        $rel = $v.FullName.Replace((Get-Location).Path + '\', '')
        if ($usedKeys[$key] -notcontains $rel) { $usedKeys[$key] += $rel }
    }
}
$report += "## 2. Keys Used Summary"
$report += "- Total unique keys used in views: $($usedKeys.Count)"
$report += ""

# ============================================================
# 3. KEYS DEFINED PER LOCALE
# ============================================================
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
        $report += "### Locale: $loc - $($keys.Count) keys defined"
    } else {
        $report += "### Locale: $loc - FILE NOT FOUND: $path"
    }
}
$report += ""

# ============================================================
# 4. MISSING KEYS (used in views but not in en/messages.php)
# ============================================================
$report += "## 3. Missing Keys (used in views but NOT in en/messages.php) - PRIORITY 1"
$report += ""
$enKeys = $localeKeys['en']
$missingInEn = @($usedKeys.Keys | Where-Object { -not $enKeys.ContainsKey($_) } | Sort-Object)

if ($missingInEn.Count -eq 0) {
    $report += "[PASS] All keys used are defined in en/messages.php"
} else {
    $report += "**Total: $($missingInEn.Count) missing keys**"
    $report += ""
    $report += "| Key | Used In |"
    $report += "|-----|---------|"
    foreach ($key in $missingInEn) {
        $files = ($usedKeys[$key] | Select-Object -First 2) -join '<br>'
        if ($usedKeys[$key].Count -gt 2) { $files += " <br>(+$($usedKeys[$key].Count - 2) more)" }
        $report += "| ``$key`` | $files |"
    }
}
$report += ""

# ============================================================
# 5. LOCALE COVERAGE GAPS
# ============================================================
$report += "## 4. Locale Coverage Gaps (in en but missing elsewhere) - PRIORITY 2"
$report += ""
foreach ($loc in @('np', 'hi', 'zh', 'ru')) {
    if (-not $localeKeys.ContainsKey($loc)) {
        $report += "### $loc - FILE MISSING"
        $report += ""
        continue
    }
    $missing = @($enKeys.Keys | Where-Object { -not $localeKeys[$loc].ContainsKey($_) } | Sort-Object)
    $report += "### $loc - $($missing.Count) missing keys"
    if ($missing.Count -gt 0) {
        foreach ($m in ($missing | Select-Object -First 30)) {
            $report += "- ``$m``"
        }
        if ($missing.Count -gt 30) {
            $report += "- ... and $($missing.Count - 30) more"
        }
    }
    $report += ""
}

# ============================================================
# 6. ANTI-PATTERN: __() ?? 'fallback'
# ============================================================
$report += "## 5. Anti-Pattern: __(messages.xxx) ?? fallback - PRIORITY 1"
$report += ""
$report += "These hide missing keys. Remove ?? fallback after ensuring key exists."
$report += ""
$antiCount = 0
foreach ($v in $views) {
    $rel = $v.FullName.Replace((Get-Location).Path + '\', '')
    $lines = Get-Content $v.FullName -Encoding UTF8
    for ($i = 0; $i -lt $lines.Count; $i++) {
        if ($lines[$i] -match "__\('messages\.\w+'\s*\?\?") {
            $report += "- $rel : line $($i+1) : ``$($lines[$i].Trim())``"
            $antiCount++
        }
    }
}
if ($antiCount -eq 0) { $report += "[PASS] No anti-patterns found" }
$report += ""

# ============================================================
# 7. HARDCODED TEXT (heuristic)
# ============================================================
$report += "## 6. Hardcoded Text (heuristic) - PRIORITY 2"
$report += ""
$report += "**Note:** Heuristic - not all need fixing (proper nouns, brand names OK)."
$report += ""
foreach ($v in $views) {
    $rel = $v.FullName.Replace((Get-Location).Path + '\', '')
    $lines = Get-Content $v.FullName -Encoding UTF8
    $findings = @()
    for ($i = 0; $i -lt $lines.Count; $i++) {
        $line = $lines[$i]
        if ($line -match '__\(' -or $line -match '^\s*<!--' -or $line -match '^\s*\{\{' -or $line -match '@') { continue }
        if ($line.Length -gt 400) { continue }
        if ($line -match '>[A-Za-z][A-Za-z0-9 ,''.?!:;()-]{4,}<') {
            $findings += "  - line $($i+1): $($line.Trim())"
        }
    }
    if ($findings.Count -gt 0) {
        $report += "### $rel - $($findings.Count) potential"
        $report += ($findings | Select-Object -First 20)
        if ($findings.Count -gt 20) { $report += "  - ... and $($findings.Count - 20) more" }
        $report += ""
    }
}

# ============================================================
# 8. UNICODE / PDF ISSUES
# ============================================================
$report += "## 7. Unicode / PDF Issues"
$report += ""
$unicodeFound = $false
$infoChar = [char]0x24D8   # info-circle (U+24D8)
foreach ($v in $views) {
    $rel = $v.FullName.Replace((Get-Location).Path + '\', '')
    $content = Get-Content $v.FullName -Raw -Encoding UTF8
    if ($content.Contains($infoChar)) {
        $report += "- $rel : contains info-circle (U+24D8) - renders as ? in PDF"
        $unicodeFound = $true
    }
}
if (-not $unicodeFound) { $report += "[PASS] No unicode PDF issues found" }
$report += ""

# ============================================================
# SAVE
# ============================================================
$reportPath = 'docs\i18n-audit-report.md'
if (-not (Test-Path 'docs')) { New-Item -ItemType Directory -Path 'docs' | Out-Null }
$report -join "`r`n" | Out-File -FilePath $reportPath -Encoding UTF8

Write-Host ""
Write-Host "[PASS] Report saved: $reportPath" -ForegroundColor Green
Write-Host ""
Write-Host "Summary:" -ForegroundColor Cyan
Write-Host "  Views audited: $($views.Count)"
Write-Host "  Keys used in views: $($usedKeys.Count)"
Write-Host "  Missing in en: $($missingInEn.Count)"
Write-Host "  Anti-patterns: $antiCount"