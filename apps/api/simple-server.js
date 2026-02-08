const express = require('express');
const app = express();
const PORT = process.env.API_PORT || 3003;

app.use(express.json());

// Health check endpoint
app.get('/api/v1/health', (req, res) => {
  res.json({ 
    status: 'ok', 
    timestamp: new Date().toISOString(),
    service: 'affiliate-api'
  });
});

// Root endpoint
app.get('/', (req, res) => {
  res.json({ 
    message: 'Affiliate API Server',
    version: '1.0.0',
    endpoints: ['/api/v1/health']
  });
});

app.listen(PORT, () => {
  console.log(`🚀 API Server running on http://localhost:${PORT}`);
  console.log(`📡 Health check: http://localhost:${PORT}/api/v1/health`);
});
