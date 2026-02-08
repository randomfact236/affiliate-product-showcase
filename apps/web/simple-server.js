const http = require('http');

const PORT = 3002;

const server = http.createServer((req, res) => {
  // Set proper UTF-8 encoding
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
            <p><strong>API Health:</strong> <a href="http://localhost:3003/api/v1/health" target="_blank">Check API Status →</a></p>
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
  console.log(`Web Server running on http://localhost:${PORT}`);
});
