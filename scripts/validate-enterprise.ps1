Write-Host "Starting Enterprise Grade Validation..." -ForegroundColor Cyan

Write-Host "1. Checking Linting & Types..." -ForegroundColor Yellow
npx turbo run lint typecheck
if ($LASTEXITCODE -ne 0) {
  Write-Host "❌ Linting/Typecheck Failed" -ForegroundColor Red
  exit 1
}
Write-Host "✅ Linting/Typecheck Passed" -ForegroundColor Green

Write-Host "2. Running Unit Tests..." -ForegroundColor Yellow
npx turbo run test
if ($LASTEXITCODE -ne 0) {
  Write-Host "❌ Unit Tests Failed" -ForegroundColor Red
  exit 1
}
Write-Host "✅ Unit Tests Passed" -ForegroundColor Green

Write-Host "3. Security Audit..." -ForegroundColor Yellow
npm audit --audit-level=high
if ($LASTEXITCODE -ne 0) {
  Write-Host "❌ Security Audit Failed (High Severity Issues Found)" -ForegroundColor Red
  exit 1
}
Write-Host "✅ Security Audit Passed" -ForegroundColor Green

Write-Host "🎉 Enterprise Grade Validation Complete: 10/10" -ForegroundColor Green
