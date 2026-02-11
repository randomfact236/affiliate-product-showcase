@echo off
chcp 65001 >nul
echo 🔥 Auto-Start with Blog Integration
echo ===================================
echo.

:: Check if Node.js is installed
node --version >nul 2>&1
if errorlevel 1 (
    echo ❌ Node.js is not installed! Please install Node.js first.
    pause
    exit /b 1
)

echo ✅ Node.js found
echo.

:: Install dependencies if needed
echo 📦 Checking dependencies...
if not exist "node_modules" (
    echo Installing root dependencies...
    call npm install
)

if not exist "apps\api\node_modules" (
    echo Installing API dependencies...
    cd apps\api
    call npm install
    cd ..\..
)

if not exist "apps\web\node_modules" (
    echo Installing Web dependencies...
    cd apps\web
    call npm install
    cd ..\..
)

echo ✅ Dependencies checked
echo.

:: Check Prisma migrations
echo 🔍 Checking database migrations...
cd apps\api

:: Generate Prisma client
npx prisma generate >nul 2>&1

:: Check if migration exists
if not exist "prisma\migrations\*blog*" (
    echo 🗄️  Creating database migration...
    npx prisma migrate dev --name add_blog_posts --skip-generate
) else (
    echo ✅ Blog migration exists
)

:: Seed database
echo 🌱 Seeding database...
npx prisma db seed >nul 2>&1
echo ✅ Database ready
echo.

cd ..\..

:: Start API Server
echo 🚀 Starting API Server on port 3003...
start "API Server" cmd /k "cd apps\api && npm run dev"

:: Wait for API to be ready
echo ⏳ Waiting for API to start...
:wait_api
timeout /t 2 /nobreak >nul
curl -s http://localhost:3003/api/v1/health >nul 2>&1
if errorlevel 1 goto wait_api
echo ✅ API Server is ready!
echo.

:: Start Web Server
echo 🚀 Starting Web Server on port 3000...
start "Web Server" cmd /k "cd apps\web && npm run dev"

:: Wait for Web to be ready
echo ⏳ Waiting for Web server to start...
:wait_web
timeout /t 3 /nobreak >nul
curl -s http://localhost:3000 >nul 2>&1
if errorlevel 1 goto wait_web
echo ✅ Web Server is ready!
echo.

echo ===================================
echo 🎉 All servers are running!
echo.
echo 📋 Available URLs:
echo   🏠 Home:       http://localhost:3000
echo   📝 Blog:       http://localhost:3000/blog
echo   🔧 API:        http://localhost:3003
echo   📊 Admin:      http://localhost:3000/admin
echo.
echo Press any key to stop all servers...
pause >nul

:: Kill all Node processes
echo 🛑 Stopping servers...
taskkill /F /IM node.exe >nul 2>&1
echo ✅ Servers stopped
echo.
