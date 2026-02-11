# Blog Integration Verification Script
Write-Host "🔍 Verifying Blog Integration..." -ForegroundColor Cyan
$issues = @()

# Check Navbar
if (Test-Path "apps/web/src/components/layout/navbar.tsx") {
    $content = Get-Content "apps/web/src/components/layout/navbar.tsx" -Raw
    if ($content -match 'href: "/blog"') {
        Write-Host "  ✅ Navbar: Blog menu found" -ForegroundColor Green
    } else {
        Write-Host "  ❌ Navbar: Blog menu MISSING" -ForegroundColor Red
        $issues += "Navbar missing blog menu"
    }
}

# Check Mobile Footer
if (Test-Path "apps/web/src/components/layout/mobile-footer-nav.tsx") {
    $content = Get-Content "apps/web/src/components/layout/mobile-footer-nav.tsx" -Raw
    if ($content -match 'href: "/blog"') {
        Write-Host "  ✅ Mobile Footer: Blog menu found" -ForegroundColor Green
    } else {
        Write-Host "  ❌ Mobile Footer: Blog menu MISSING" -ForegroundColor Red
        $issues += "Mobile footer missing blog menu"
    }
}

# Check Mobile Drawer
if (Test-Path "apps/web/src/components/layout/mobile-menu-drawer.tsx") {
    $content = Get-Content "apps/web/src/components/layout/mobile-menu-drawer.tsx" -Raw
    if ($content -match 'href: "/blog"') {
        Write-Host "  ✅ Mobile Drawer: Blog menu found" -ForegroundColor Green
    } else {
        Write-Host "  ❌ Mobile Drawer: Blog menu MISSING" -ForegroundColor Red
        $issues += "Mobile drawer missing blog menu"
    }
}

# Check Pages
if (Test-Path "apps/web/src/app/blog/page.tsx") {
    Write-Host "  ✅ Blog List Page: Exists" -ForegroundColor Green
} else {
    Write-Host "  ❌ Blog List Page: MISSING" -ForegroundColor Red
    $issues += "Blog list page not found"
}

if (Test-Path "apps/web/src/app/blog/[slug]/page.tsx") {
    Write-Host "  ✅ Blog Single Page: Exists" -ForegroundColor Green
} else {
    Write-Host "  ❌ Blog Single Page: MISSING" -ForegroundColor Red
    $issues += "Blog single page not found"
}

if (Test-Path "apps/web/src/lib/api/blog.ts") {
    Write-Host "  ✅ Blog API Client: Exists" -ForegroundColor Green
} else {
    Write-Host "  ❌ Blog API Client: MISSING" -ForegroundColor Red
    $issues += "Blog API client not found"
}

# Summary
Write-Host ""
if ($issues.Count -eq 0) {
    Write-Host "✅ All checks passed! Blog menu is properly configured." -ForegroundColor Green
} else {
    Write-Host "❌ Found issues:" -ForegroundColor Red
    $issues | ForEach-Object { Write-Host "  • $_" -ForegroundColor Red }
}

Write-Host ""
Write-Host "📋 Navigation Structure:" -ForegroundColor Cyan
Write-Host "  • Desktop Navbar: Home | Blog | Products | Admin"
Write-Host "  • Mobile Footer: Home | Blog | Search | Filter | Menu"
Write-Host "  • Mobile Drawer: Home | Blog | Products | Contact | Admin"

Write-Host ""
Write-Host "🔗 Blog URLs:" -ForegroundColor Cyan
Write-Host "  • List: http://localhost:3000/blog"
Write-Host "  • Post: http://localhost:3000/blog/slug"
