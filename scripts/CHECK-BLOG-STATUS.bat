@echo off
chcp 65001 >nul
echo.
echo 🔍 BLOG INTEGRATION STATUS CHECK
echo =================================
echo.

echo 📁 Checking File Structure...
echo.

:: Check Navbar
findstr /C:"/blog" apps\web\src\components\layout\navbar.tsx >nul 2>&1
if %errorlevel% == 0 (
    echo   ✅ Desktop Navbar: Blog menu found
) else (
    echo   ❌ Desktop Navbar: Blog menu MISSING
)

:: Check Mobile Footer
findstr /C:"/blog" apps\web\src\components\layout\mobile-footer-nav.tsx >nul 2>&1
if %errorlevel% == 0 (
    echo   ✅ Mobile Footer: Blog menu found
) else (
    echo   ❌ Mobile Footer: Blog menu MISSING
)

:: Check Mobile Drawer
findstr /C:"/blog" apps\web\src\components\layout\mobile-menu-drawer.tsx >nul 2>&1
if %errorlevel% == 0 (
    echo   ✅ Mobile Drawer: Blog menu found
) else (
    echo   ❌ Mobile Drawer: Blog menu MISSING
)

:: Check Pages
if exist "apps\web\src\app\blog\page.tsx" (
    echo   ✅ Blog List Page: Exists
) else (
    echo   ❌ Blog List Page: MISSING
)

if exist "apps\web\src\app\blog\[slug]\page.tsx" (
    echo   ✅ Blog Single Page: Exists
) else (
    echo   ❌ Blog Single Page: MISSING
)

if exist "apps\web\src\lib\api\blog.ts" (
    echo   ✅ Blog API Client: Exists
) else (
    echo   ❌ Blog API Client: MISSING
)

echo.
echo 🌐 Checking Server Status...
echo.

:: Check API Server
curl -s http://localhost:3003/api/v1/health >nul 2>&1
if %errorlevel% == 0 (
    echo   ✅ API Server: Running on port 3003
) else (
    echo   ⚠️  API Server: NOT RUNNING on port 3003
)

:: Check Web Server
curl -s http://localhost:3000 >nul 2>&1
if %errorlevel% == 0 (
    echo   ✅ Web Server: Running on port 3000
) else (
    echo   ⚠️  Web Server: NOT RUNNING on port 3000
)

echo.
echo 🗄️  Checking Database...
echo.

:: Check if Prisma client exists
if exist "apps\api\node_modules\.prisma\client\index.js" (
    echo   ✅ Prisma Client: Generated
) else (
    echo   ⚠️  Prisma Client: NOT GENERATED
    echo      Run: cd apps\api ^&^& npx prisma generate
)

:: Check migrations
if exist "apps\api\prisma\migrations\*blog*" (
    echo   ✅ Blog Migration: Exists
) else (
    echo   ⚠️  Blog Migration: NOT FOUND
    echo      Run: cd apps\api ^&^& npx prisma migrate dev --name add_blog_posts
)

echo.
echo =================================
echo 📋 NAVIGATION STRUCTURE:
echo.
echo   Desktop Navbar:
echo     Home ^| Blog ^| Products ^| Admin
echo.
echo   Mobile Footer:
echo     Home ^| Blog ^| Search ^| Filter ^| Menu
echo.
echo   Mobile Menu Drawer:
echo     Home ^| Blog ^| Products ^| Contact ^| Admin
echo.
echo =================================
echo 🔗 BLOG URLS:
echo.
echo   Blog List:   http://localhost:3000/blog
 echo   Blog Post:   http://localhost:3000/blog/slug
echo.
echo =================================
echo 🚀 QUICK START:
echo.
echo   Option 1 - Auto Start:
echo     RUN-SERVER.bat
echo.
echo   Option 2 - Manual Start:
echo     Terminal 1: cd apps\api ^&^& npm run dev
 echo     Terminal 2: cd apps\web ^&^& npm run dev
echo.

pause
