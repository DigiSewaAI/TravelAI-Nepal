@echo off
cls
echo ============================================================
echo   TravelAI Nepal — i18n Audit
echo ============================================================
echo.

powershell -ExecutionPolicy Bypass -File "%~dp0i18n-audit.ps1"

echo.
pause