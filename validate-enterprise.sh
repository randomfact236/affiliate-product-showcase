#!/bin/bash
echo "Starting Enterprise Grade Validation..."

echo "1. Checking Linting & Types..."
npx turbo run lint typecheck
if [ $? -ne 0 ]; then
  echo "❌ Linting/Typecheck Failed"
  exit 1
fi
echo "✅ Linting/Typecheck Passed"

echo "2. Running Unit Tests..."
npx turbo run test
if [ $? -ne 0 ]; then
  echo "❌ Unit Tests Failed"
  exit 1
fi
echo "✅ Unit Tests Passed"

echo "3. Security Audit..."
npm audit --audit-level=high
if [ $? -ne 0 ]; then
  echo "❌ Security Audit Failed (High Severity Issues Found)"
  exit 1
fi
echo "✅ Security Audit Passed"

echo "🎉 Enterprise Grade Validation Complete: 10/10"
