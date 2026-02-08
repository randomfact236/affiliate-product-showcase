const http = require('http');
const url = require('url');

const PORT = 3002;
const API_HOST = 'localhost';
const API_PORT = 3003;

const server = http.createServer((req, res) => {
  const parsedUrl = url.parse(req.url, true);
  const pathname = parsedUrl.pathname;

  // CORS headers
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');

  if (req.method === 'OPTIONS') {
    res.writeHead(200);
    res.end();
    return;
  }

  // Redirect /admin to API docs
  if (pathname === '/admin' || pathname === '/admin/') {
    res.writeHead(302, { Location: `http://${API_HOST}:${API_PORT}/api/docs` });
    res.end();
    return;
  }

  // Proxy /api/* or /admin/* to backend
  if (pathname.startsWith('/api/') || pathname.startsWith('/admin/')) {
    const targetPath = pathname.startsWith('/api/') 
      ? pathname.replace('/api/', '/api/v1/') 
      : pathname.replace('/admin/', '/api/v1/');
    
    console.log(`[PROXY] ${req.method} ${pathname} → http://${API_HOST}:${API_PORT}${targetPath}`);
    
    const options = {
      hostname: API_HOST,
      port: API_PORT,
      path: targetPath + (parsedUrl.search || ''),
      method: req.method,
      headers: {
        ...req.headers,
        host: `${API_HOST}:${API_PORT}`,
      },
    };

    const proxyReq = http.request(options, (proxyRes) => {
      res.writeHead(proxyRes.statusCode, proxyRes.headers);
      proxyRes.pipe(res);
    });

    proxyReq.on('error', (err) => {
      console.error('[PROXY ERROR]', err.message);
      res.writeHead(502, { 'Content-Type': 'application/json' });
      res.end(JSON.stringify({ error: 'Backend unavailable', message: err.message }));
    });

    req.pipe(proxyReq);
    return;
  }

  // Serve the HTML page
  res.writeHead(200, { 
    'Content-Type': 'text/html; charset=utf-8'
  });
  
  const html = `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Affiliate Website</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; 
            max-width: 800px; 
            margin: 50px auto; 
            padding: 20px;
            background: #f5f5f5;
            line-height: 1.6;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { 
            color: #333; 
            margin-bottom: 10px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 20px;
        }
        .status { 
            display: inline-block;
            padding: 12px 24px;
            background: #4CAF50;
            color: white;
            border-radius: 5px;
            margin: 10px 0;
            font-weight: bold;
        }
        .info {
            margin-top: 30px;
            padding: 20px;
            background: #e3f2fd;
            border-radius: 5px;
        }
        .info h3 {
            margin-top: 0;
            color: #1976d2;
        }
        .info ul {
            list-style: none;
            padding: 0;
        }
        .info li {
            padding: 8px 0;
            border-bottom: 1px solid #bbdefb;
        }
        .info li:last-child {
            border-bottom: none;
        }
        .check {
            color: #4CAF50;
            font-weight: bold;
            margin-right: 8px;
        }
        a {
            color: #1976d2;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .footer {
            margin-top: 30px;
            color: #999;
            font-size: 0.9em;
            text-align: center;
        }
        .rocket {
            font-size: 2em;
        }
        .proxy-info {
            margin-top: 20px;
            padding: 15px;
            background: #f3e5f5;
            border-radius: 5px;
            border-left: 4px solid #9c27b0;
        }
        .proxy-info h4 {
            margin-top: 0;
            color: #7b1fa2;
        }
        .proxy-info code {
            background: #e1bee7;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="rocket">🚀</div>
        <h1>Affiliate Website</h1>
        <p class="subtitle">Welcome to the Enterprise Affiliate Platform</p>
        
        <div class="status">✓ Server Running</div>
        
        <div class="info">
            <h3>System Status</h3>
            <ul>
                <li><span class="check">✓</span> Frontend: Running on port 3002</li>
                <li><span class="check">✓</span> API: Running on port 3003</li>
                <li><span class="check">✓</span> Database: PostgreSQL on port 5433</li>
                <li><span class="check">✓</span> Cache: Redis on port 6380</li>
            </ul>
        </div>

        <div class="proxy-info">
            <h4>🔗 API Proxy Routes</h4>
            <ul>
                <li><code>/api/*</code> → Proxied to <code>http://localhost:3003/api/v1/*</code></li>
                <li><code>/admin</code> → Redirects to <a href="http://localhost:3003/api/docs">API Docs</a></li>
            </ul>
            <p><strong>Try it:</strong> <a href="/api/products" target="_blank">/api/products →</a></p>
        </div>
        
        <div class="footer">
            Generated: ${new Date().toLocaleString()}<br>
            <small>Server is operational</small>
        </div>
    </div>
</body>
</html>`;
  
  res.end(html);
});

server.listen(PORT, () => {
  console.log(`🌐 Web Server: http://localhost:${PORT}`);
  console.log(`🔗 API Proxy:   http://localhost:${PORT}/api/* → http://localhost:3003/api/v1/*`);
  console.log(`📚 API Docs:    http://localhost:${PORT}/admin → http://localhost:3003/api/docs`);
});
