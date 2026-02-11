const express = require('express');
const cors = require('cors');
const app = express();
const PORT = process.env.API_PORT || 3003;

app.use(cors());
app.use(express.json());

// Mock data for ribbons
const ribbons = [
  { id: '1', name: 'Featured', label: 'Featured', description: 'Featured products', color: '#FFFFFF', bgColor: '#3B82F6', icon: 'star', position: 'TOP_RIGHT', priority: 1, isActive: true, createdAt: new Date(), updatedAt: new Date() },
  { id: '2', name: 'New', label: 'New Arrival', description: 'New products', color: '#FFFFFF', bgColor: '#10B981', icon: 'sparkles', position: 'TOP_LEFT', priority: 2, isActive: true, createdAt: new Date(), updatedAt: new Date() },
  { id: '3', name: 'Sale', label: 'On Sale', description: 'Sale items', color: '#FFFFFF', bgColor: '#EF4444', icon: 'tag', position: 'TOP_RIGHT', priority: 3, isActive: true, createdAt: new Date(), updatedAt: new Date() },
  { id: '4', name: 'Best Seller', label: 'Best Seller', description: 'Popular items', color: '#FFFFFF', bgColor: '#F59E0B', icon: 'trending-up', position: 'TOP_RIGHT', priority: 4, isActive: true, createdAt: new Date(), updatedAt: new Date() },
];

// Mock data for tags
const tags = [
  { id: '1', slug: 'wireless', name: 'Wireless', description: 'Wireless products', color: '#3B82F6', icon: 'wifi', productCount: 15, isActive: true, sortOrder: 1, createdAt: new Date(), updatedAt: new Date() },
  { id: '2', slug: 'bluetooth', name: 'Bluetooth', description: 'Bluetooth enabled', color: '#10B981', icon: 'bluetooth', productCount: 12, isActive: true, sortOrder: 2, createdAt: new Date(), updatedAt: new Date() },
  { id: '3', slug: 'sale', name: 'Sale', description: 'On sale items', color: '#EF4444', icon: 'tag', productCount: 8, isActive: true, sortOrder: 3, createdAt: new Date(), updatedAt: new Date() },
  { id: '4', slug: 'premium', name: 'Premium', description: 'Premium quality', color: '#8B5CF6', icon: 'crown', productCount: 5, isActive: true, sortOrder: 4, createdAt: new Date(), updatedAt: new Date() },
];

// Mock data for media
const media = [
  { id: '1', filename: 'product-001.jpg', originalUrl: 'https://via.placeholder.com/400x400/3B82F6/FFFFFF?text=Product+1', mimeType: 'image/jpeg', fileSize: 1024000, width: 800, height: 800, conversionStatus: 'COMPLETED', isConverted: true, thumbnailUrl: 'https://via.placeholder.com/150x150/3B82F6/FFFFFF?text=Thumb+1', createdAt: new Date() },
  { id: '2', filename: 'product-002.jpg', originalUrl: 'https://via.placeholder.com/400x400/10B981/FFFFFF?text=Product+2', mimeType: 'image/jpeg', fileSize: 2048000, width: 1200, height: 1200, conversionStatus: 'PENDING', isConverted: false, thumbnailUrl: null, createdAt: new Date() },
  { id: '3', filename: 'banner-hero.jpg', originalUrl: 'https://via.placeholder.com/800x400/F59E0B/FFFFFF?text=Banner', mimeType: 'image/jpeg', fileSize: 5120000, width: 1920, height: 1080, conversionStatus: 'COMPLETED', isConverted: true, thumbnailUrl: 'https://via.placeholder.com/300x150/F59E0B/FFFFFF?text=Thumb', createdAt: new Date() },
];

// Mock data for products
const products = [
  { id: '1', name: 'Premium Wireless Headphones', slug: 'premium-wireless-headphones', description: 'High-quality wireless headphones with noise cancellation', shortDescription: 'Best wireless headphones', status: 'PUBLISHED', price: 19999, comparePrice: 24999, image: 'https://via.placeholder.com/100x100/3B82F6/FFFFFF?text=HP', category: { id: '1', name: 'Electronics', slug: 'electronics' }, tags: [{ id: '1', name: 'Wireless' }, { id: '2', name: 'Bluetooth' }], ribbon: { id: '4', name: 'Best Seller', label: 'Best Seller', bgColor: '#F59E0B', color: '#FFFFFF' }, isFeatured: true, createdAt: new Date('2024-01-15'), updatedAt: new Date() },
  { id: '2', name: 'Ultra Slim Laptop Pro', slug: 'ultra-slim-laptop-pro', description: 'Lightweight laptop with powerful performance', shortDescription: 'Professional laptop', status: 'PUBLISHED', price: 99900, comparePrice: 109900, image: 'https://via.placeholder.com/100x100/10B981/FFFFFF?text=LT', category: { id: '2', name: 'Computers', slug: 'computers' }, tags: [{ id: '3', name: 'Sale' }], ribbon: { id: '3', name: 'Sale', label: 'On Sale', bgColor: '#EF4444', color: '#FFFFFF' }, isFeatured: false, createdAt: new Date('2024-01-20'), updatedAt: new Date() },
  { id: '3', name: 'Smart Watch Series 5', slug: 'smart-watch-series-5', description: 'Advanced fitness tracking smartwatch', shortDescription: 'Fitness smartwatch', status: 'DRAFT', price: 29999, comparePrice: null, image: 'https://via.placeholder.com/100x100/8B5CF6/FFFFFF?text=SW', category: { id: '1', name: 'Electronics', slug: 'electronics' }, tags: [{ id: '4', name: 'Premium' }], ribbon: null, isFeatured: false, createdAt: new Date('2024-02-01'), updatedAt: new Date() },
  { id: '4', name: 'Bluetooth Speaker Mini', slug: 'bluetooth-speaker-mini', description: 'Portable speaker with amazing sound', shortDescription: 'Portable speaker', status: 'DRAFT', price: 4999, comparePrice: 5999, image: 'https://via.placeholder.com/100x100/F59E0B/FFFFFF?text=SP', category: { id: '1', name: 'Electronics', slug: 'electronics' }, tags: [{ id: '2', name: 'Bluetooth' }], ribbon: { id: '2', name: 'New', label: 'New Arrival', bgColor: '#10B981', color: '#FFFFFF' }, isFeatured: true, createdAt: new Date('2024-02-05'), updatedAt: new Date() },
  { id: '5', name: 'Gaming Mouse RGB', slug: 'gaming-mouse-rgb', description: 'High-precision gaming mouse', shortDescription: 'Gaming mouse', status: 'PUBLISHED', price: 7999, comparePrice: null, image: 'https://via.placeholder.com/100x100/EF4444/FFFFFF?text=GM', category: { id: '2', name: 'Computers', slug: 'computers' }, tags: [{ id: '4', name: 'Premium' }], ribbon: { id: '1', name: 'Featured', label: 'Featured', bgColor: '#3B82F6', color: '#FFFFFF' }, isFeatured: true, createdAt: new Date('2024-02-10'), updatedAt: new Date() },
];

// Health check endpoint
app.get('/api/v1/health', (req, res) => {
  res.json({ 
    status: 'ok', 
    timestamp: new Date().toISOString(),
    service: 'affiliate-api'
  });
});

// ========== RIBBONS API ==========
app.get('/ribbons', (req, res) => {
  res.json({
    items: ribbons,
    total: ribbons.length,
    page: 1,
    limit: 20,
    totalPages: 1
  });
});

app.get('/ribbons/active', (req, res) => {
  res.json(ribbons.filter(r => r.isActive));
});

app.get('/ribbons/:id', (req, res) => {
  const ribbon = ribbons.find(r => r.id === req.params.id);
  if (!ribbon) return res.status(404).json({ message: 'Ribbon not found' });
  res.json(ribbon);
});

app.post('/ribbons', (req, res) => {
  const newRibbon = { ...req.body, id: Date.now().toString(), createdAt: new Date(), updatedAt: new Date() };
  ribbons.push(newRibbon);
  res.status(201).json(newRibbon);
});

app.put('/ribbons/:id', (req, res) => {
  const index = ribbons.findIndex(r => r.id === req.params.id);
  if (index === -1) return res.status(404).json({ message: 'Ribbon not found' });
  ribbons[index] = { ...ribbons[index], ...req.body, updatedAt: new Date() };
  res.json(ribbons[index]);
});

app.patch('/ribbons/:id/toggle-active', (req, res) => {
  const index = ribbons.findIndex(r => r.id === req.params.id);
  if (index === -1) return res.status(404).json({ message: 'Ribbon not found' });
  ribbons[index].isActive = !ribbons[index].isActive;
  ribbons[index].updatedAt = new Date();
  res.json(ribbons[index]);
});

app.delete('/ribbons/:id', (req, res) => {
  const index = ribbons.findIndex(r => r.id === req.params.id);
  if (index === -1) return res.status(404).json({ message: 'Ribbon not found' });
  ribbons.splice(index, 1);
  res.status(204).send();
});

// ========== TAGS API ==========
app.get('/tags', (req, res) => {
  res.json({
    items: tags,
    total: tags.length,
    page: 1,
    limit: 20,
    totalPages: 1
  });
});

app.get('/tags/active', (req, res) => {
  res.json(tags.filter(t => t.isActive));
});

app.get('/tags/:id', (req, res) => {
  const tag = tags.find(t => t.id === req.params.id);
  if (!tag) return res.status(404).json({ message: 'Tag not found' });
  res.json(tag);
});

app.post('/tags', (req, res) => {
  const newTag = { ...req.body, id: Date.now().toString(), productCount: 0, createdAt: new Date(), updatedAt: new Date() };
  tags.push(newTag);
  res.status(201).json(newTag);
});

app.put('/tags/:id', (req, res) => {
  const index = tags.findIndex(t => t.id === req.params.id);
  if (index === -1) return res.status(404).json({ message: 'Tag not found' });
  tags[index] = { ...tags[index], ...req.body, updatedAt: new Date() };
  res.json(tags[index]);
});

app.patch('/tags/:id/toggle-active', (req, res) => {
  const index = tags.findIndex(t => t.id === req.params.id);
  if (index === -1) return res.status(404).json({ message: 'Tag not found' });
  tags[index].isActive = !tags[index].isActive;
  tags[index].updatedAt = new Date();
  res.json(tags[index]);
});

app.delete('/tags/:id', (req, res) => {
  const index = tags.findIndex(t => t.id === req.params.id);
  if (index === -1) return res.status(404).json({ message: 'Tag not found' });
  tags.splice(index, 1);
  res.status(204).send();
});

app.post('/tags/merge', (req, res) => {
  const { sourceTagIds, targetTagId } = req.body;
  res.json({ message: `Merged ${sourceTagIds.length} tags`, merged: sourceTagIds.length });
});

// ========== MEDIA API ==========
app.get('/media', (req, res) => {
  res.json({
    items: media,
    total: media.length,
    page: 1,
    limit: 20,
    totalPages: 1
  });
});

app.get('/media/stats', (req, res) => {
  const totalImages = media.length;
  const fullyOptimized = media.filter(m => m.isConverted).length;
  const needsConversion = media.filter(m => !m.isConverted).length;
  const storageSaved = media.reduce((acc, m) => acc + (m.isConverted ? m.fileSize * 0.3 : 0), 0);
  
  res.json({
    totalImages,
    fullyOptimized,
    needsConversion,
    storageSaved,
    storageSavedFormatted: `${(storageSaved / 1024 / 1024).toFixed(1)} MB`,
    optimizationPercentage: Math.round((fullyOptimized / totalImages) * 100)
  });
});

app.get('/media/unconverted', (req, res) => {
  res.json(media.filter(m => !m.isConverted));
});

app.post('/media', (req, res) => {
  const newMedia = { ...req.body, id: Date.now().toString(), createdAt: new Date(), updatedAt: new Date() };
  media.push(newMedia);
  res.status(201).json(newMedia);
});

// ========== PRODUCTS API ==========
app.get('/products', (req, res) => {
  const { status, category, search, featured, page = 1, limit = 10 } = req.query;
  
  let filtered = [...products];
  
  if (status) filtered = filtered.filter(p => p.status === status);
  if (category) filtered = filtered.filter(p => p.category?.id === category);
  if (featured === 'true') filtered = filtered.filter(p => p.isFeatured);
  if (search) {
    const q = search.toLowerCase();
    filtered = filtered.filter(p => p.name.toLowerCase().includes(q) || p.slug.toLowerCase().includes(q));
  }
  
  const total = filtered.length;
  const start = (page - 1) * limit;
  const items = filtered.slice(start, start + parseInt(limit));
  
  res.json({
    items,
    total,
    page: parseInt(page),
    limit: parseInt(limit),
    totalPages: Math.ceil(total / limit)
  });
});

app.get('/products/stats', (req, res) => {
  const stats = {
    all: products.length,
    published: products.filter(p => p.status === 'PUBLISHED').length,
    draft: products.filter(p => p.status === 'DRAFT').length,
    trash: products.filter(p => p.status === 'ARCHIVED').length,
  };
  res.json(stats);
});

app.get('/products/:id', (req, res) => {
  const product = products.find(p => p.id === req.params.id);
  if (!product) return res.status(404).json({ message: 'Product not found' });
  res.json(product);
});

app.post('/products', (req, res) => {
  const newProduct = { 
    ...req.body, 
    id: Date.now().toString(), 
    createdAt: new Date(), 
    updatedAt: new Date() 
  };
  products.push(newProduct);
  res.status(201).json(newProduct);
});

app.put('/products/:id', (req, res) => {
  const index = products.findIndex(p => p.id === req.params.id);
  if (index === -1) return res.status(404).json({ message: 'Product not found' });
  products[index] = { ...products[index], ...req.body, updatedAt: new Date() };
  res.json(products[index]);
});

app.delete('/products/:id', (req, res) => {
  const index = products.findIndex(p => p.id === req.params.id);
  if (index === -1) return res.status(404).json({ message: 'Product not found' });
  products[index].status = 'ARCHIVED';
  products[index].updatedAt = new Date();
  res.json(products[index]);
});

// ========== USERS API ==========
const users = [
  { id: '1', email: 'admin@example.com', firstName: 'Admin', lastName: 'User', avatar: null, status: 'ACTIVE', emailVerified: true, roles: ['ADMIN'], createdAt: new Date('2024-01-01'), updatedAt: new Date(), lastLoginAt: new Date() },
  { id: '2', email: 'editor@example.com', firstName: 'Editor', lastName: 'User', avatar: null, status: 'ACTIVE', emailVerified: true, roles: ['EDITOR'], createdAt: new Date('2024-01-02'), updatedAt: new Date(), lastLoginAt: new Date() },
  { id: '3', email: 'john@example.com', firstName: 'John', lastName: 'Doe', avatar: null, status: 'ACTIVE', emailVerified: true, roles: ['USER'], createdAt: new Date('2024-02-01'), updatedAt: new Date(), lastLoginAt: new Date() },
  { id: '4', email: 'jane@example.com', firstName: 'Jane', lastName: 'Smith', avatar: null, status: 'INACTIVE', emailVerified: false, roles: ['USER'], createdAt: new Date('2024-02-05'), updatedAt: new Date(), lastLoginAt: null },
  { id: '5', email: 'pending@example.com', firstName: 'Pending', lastName: 'User', avatar: null, status: 'PENDING_VERIFICATION', emailVerified: false, roles: ['USER'], createdAt: new Date('2024-02-10'), updatedAt: new Date(), lastLoginAt: null },
];

app.get('/users', (req, res) => {
  const { search, status, page = 1, limit = 20 } = req.query;
  
  let filtered = [...users];
  
  if (status) filtered = filtered.filter(u => u.status === status);
  if (search) {
    const q = search.toLowerCase();
    filtered = filtered.filter(u => 
      u.email.toLowerCase().includes(q) || 
      u.firstName?.toLowerCase().includes(q) || 
      u.lastName?.toLowerCase().includes(q)
    );
  }
  
  const total = filtered.length;
  const start = (page - 1) * limit;
  const items = filtered.slice(start, start + parseInt(limit));
  
  res.json({
    items,
    total,
    page: parseInt(page),
    limit: parseInt(limit),
    totalPages: Math.ceil(total / limit)
  });
});

app.get('/users/stats', (req, res) => {
  res.json({
    total: users.length,
    active: users.filter(u => u.status === 'ACTIVE').length,
    inactive: users.filter(u => u.status === 'INACTIVE').length,
    pending: users.filter(u => u.status === 'PENDING_VERIFICATION').length,
  });
});

app.get('/users/:id', (req, res) => {
  const user = users.find(u => u.id === req.params.id);
  if (!user) return res.status(404).json({ message: 'User not found' });
  res.json(user);
});

app.post('/users', (req, res) => {
  const newUser = { 
    ...req.body, 
    id: Date.now().toString(),
    createdAt: new Date(), 
    updatedAt: new Date() 
  };
  users.push(newUser);
  res.status(201).json(newUser);
});

app.put('/users/:id', (req, res) => {
  const index = users.findIndex(u => u.id === req.params.id);
  if (index === -1) return res.status(404).json({ message: 'User not found' });
  users[index] = { ...users[index], ...req.body, updatedAt: new Date() };
  res.json(users[index]);
});

app.delete('/users/:id', (req, res) => {
  const index = users.findIndex(u => u.id === req.params.id);
  if (index === -1) return res.status(404).json({ message: 'User not found' });
  users.splice(index, 1);
  res.status(204).send();
});

app.patch('/users/:id/toggle-status', (req, res) => {
  const index = users.findIndex(u => u.id === req.params.id);
  if (index === -1) return res.status(404).json({ message: 'User not found' });
  users[index].status = users[index].status === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE';
  users[index].updatedAt = new Date();
  res.json(users[index]);
});

// ========== ANALYTICS API ==========
const analyticsEvents = [];
const analyticsSessions = [];

app.post('/analytics/track', (req, res) => {
  const event = { ...req.body, id: Date.now().toString(), createdAt: new Date() };
  analyticsEvents.push(event);
  res.status(201).json(event);
});

app.post('/analytics/track/batch', (req, res) => {
  const { events } = req.body;
  const tracked = events.map(e => ({ ...e, id: Date.now().toString(), createdAt: new Date() }));
  analyticsEvents.push(...tracked);
  res.status(201).json({ tracked: tracked.length });
});

app.get('/analytics/dashboard', (req, res) => {
  const period = parseInt(req.query.period) || 30;
  
  // Calculate stats from mock data
  const productViews = products.reduce((sum, p) => sum + (p.viewCount || 0), 0);
  const totalClicks = affiliateLinks.reduce((sum, l) => sum + l.clicks, 0);
  const totalConversions = affiliateLinks.reduce((sum, l) => sum + l.conversions, 0);
  
  res.json({
    totalViews: productViews + 1250,
    totalClicks: totalClicks + 340,
    totalConversions: totalConversions + 28,
    conversionRate: 3.2,
    totalRevenue: 45230,
    avgRevenuePerConversion: 1615,
    uniqueVisitors: 890,
    avgSessionDuration: 245,
    bounceRate: 42.5,
    trend: Array.from({ length: 7 }, (_, i) => ({
      date: new Date(Date.now() - (6-i) * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
      views: 150 + Math.floor(Math.random() * 100),
      clicks: 40 + Math.floor(Math.random() * 30),
      conversions: 3 + Math.floor(Math.random() * 5),
      revenue: 5000 + Math.floor(Math.random() * 3000),
      uniqueVisitors: 100 + Math.floor(Math.random() * 50),
      directViews: 50 + Math.floor(Math.random() * 30),
      searchViews: 60 + Math.floor(Math.random() * 40),
      socialViews: 30 + Math.floor(Math.random() * 20),
      referralViews: 10 + Math.floor(Math.random() * 10),
    })),
  });
});

app.get('/analytics/realtime', (req, res) => {
  res.json({
    activeUsers: 12,
    pageViewsLastMinute: 8,
    pageViewsLast5Minutes: 34,
    pageViewsLast15Minutes: 89,
    topPages: [
      { url: '/products/premium-wireless-headphones', views: 15 },
      { url: '/categories/electronics', views: 12 },
      { url: '/', views: 8 },
      { url: '/products/ultra-slim-laptop-pro', views: 6 },
      { url: '/search', views: 4 },
    ],
  });
});

app.get('/analytics/top-products', (req, res) => {
  res.json([
    { productId: '1', productName: 'Premium Wireless Headphones', views: 450, clicks: 120, conversions: 8, revenue: 15992, conversionRate: 1.78 },
    { productId: '2', productName: 'Ultra Slim Laptop Pro', views: 320, clicks: 85, conversions: 5, revenue: 49950, conversionRate: 1.56 },
    { productId: '5', productName: 'Gaming Mouse RGB', views: 280, clicks: 95, conversions: 12, revenue: 9594, conversionRate: 4.29 },
    { productId: '4', productName: 'Bluetooth Speaker Mini', views: 195, clicks: 45, conversions: 3, revenue: 4497, conversionRate: 1.54 },
  ]);
});

app.get('/analytics/devices', (req, res) => {
  res.json([
    { deviceType: 'desktop', count: 520, percentage: 58.4 },
    { deviceType: 'mobile', count: 310, percentage: 34.8 },
    { deviceType: 'tablet', count: 60, percentage: 6.8 },
  ]);
});

app.get('/analytics/sources', (req, res) => {
  res.json([
    { source: 'direct', views: 320, visitors: 210 },
    { source: 'google', views: 280, visitors: 195 },
    { source: 'facebook', views: 150, visitors: 120 },
    { source: 'instagram', views: 85, visitors: 65 },
    { source: 'referral', views: 55, visitors: 40 },
  ]);
});

// Mock affiliate links for analytics
const affiliateLinks = [
  { id: '1', productId: '1', platform: 'amazon', url: 'https://amazon.com/...', clicks: 245, conversions: 12 },
  { id: '2', productId: '2', platform: 'aliexpress', url: 'https://aliexpress.com/...', clicks: 180, conversions: 8 },
  { id: '3', productId: '5', platform: 'amazon', url: 'https://amazon.com/...', clicks: 320, conversions: 18 },
];

// ========== REVENUE & COMMISSION API ==========

app.get('/analytics/revenue', (req, res) => {
  res.json({
    totalRevenue: 125430,
    totalCommission: 8760,
    totalConversions: 66,
    totalClicks: 1085,
    epc: 8, // Earnings per click in cents
    avgOrderValue: 1899,
    avgCommission: 132,
    conversionRate: 6.08,
    commissionGrowth: 12.5,
    revenueGrowth: 8.3,
  });
});

app.get('/analytics/commissions', (req, res) => {
  const daily = Array.from({ length: 30 }, (_, i) => ({
    date: new Date(Date.now() - (29-i) * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
    commission: Math.floor(Math.random() * 500) + 100,
    revenue: Math.floor(Math.random() * 5000) + 1000,
    conversions: Math.floor(Math.random() * 5) + 1,
  }));
  res.json(daily);
});

app.get('/analytics/top-earners', (req, res) => {
  res.json([
    { productId: '1', productName: 'Premium Wireless Headphones', image: null, clicks: 450, conversions: 18, revenue: 359820, commission: 25187, epc: 5, conversionRate: 4.0 },
    { productId: '5', productName: 'Gaming Mouse RGB', image: null, clicks: 380, conversions: 22, revenue: 167960, commission: 11757, epc: 3, conversionRate: 5.8 },
    { productId: '2', productName: 'Ultra Slim Laptop Pro', image: null, clicks: 320, conversions: 8, revenue: 799200, commission: 55944, epc: 17, conversionRate: 2.5 },
    { productId: '4', productName: 'Bluetooth Speaker Mini', image: null, clicks: 195, conversions: 6, revenue: 29982, commission: 2099, epc: 1, conversionRate: 3.1 },
  ]);
});

// ========== FUNNEL & CAMPAIGN API ==========

app.get('/analytics/funnel', (req, res) => {
  res.json({
    stages: {
      impressions: 15000,
      clicks: 1085,
      landings: 950,
      addsToCart: 285,
      checkouts: 142,
      purchases: 66,
    },
    dropOffRates: {
      viewToClick: 92.8,
      clickToCart: 73.7,
      cartToPurchase: 76.8,
      overall: 0.44,
    },
    revenue: 125430,
  });
});

app.get('/analytics/campaigns', (req, res) => {
  res.json([
    { source: 'google', medium: 'cpc', campaign: 'tech_gadgets', impressions: 5000, clicks: 320, conversions: 12, revenue: 45000, commission: 3150, cost: 800, ctr: 6.4, roas: 56.25 },
    { source: 'facebook', medium: 'social', campaign: 'summer_sale', impressions: 8000, clicks: 280, conversions: 18, revenue: 38000, commission: 2660, cost: 500, ctr: 3.5, roas: 76.0 },
    { source: 'email', medium: 'newsletter', campaign: 'weekly_deals', impressions: 12000, clicks: 420, conversions: 24, revenue: 28500, commission: 1995, cost: 0, ctr: 3.5, roas: Infinity },
    { source: 'instagram', medium: 'influencer', campaign: 'tech_review', impressions: 3500, clicks: 180, conversions: 8, revenue: 22000, commission: 1540, cost: 1200, ctr: 5.1, roas: 18.33 },
  ]);
});

// ========== LINK PERFORMANCE API ==========

app.get('/analytics/links/:linkId/performance', (req, res) => {
  const { linkId } = req.params;
  const link = affiliateLinks.find(l => l.id === linkId);
  
  if (!link) {
    return res.status(404).json({ message: 'Link not found' });
  }
  
  const commission = link.conversions * 150; // Mock commission calculation
  
  res.json({
    linkId,
    clicks: link.clicks,
    conversions: link.conversions,
    revenue: link.conversions * 1899,
    commission,
    epc: Math.round(commission / link.clicks),
    conversionRate: ((link.conversions / link.clicks) * 100).toFixed(2),
  });
});

// ========== SEARCH & GEO API ==========

app.get('/analytics/search-terms', (req, res) => {
  res.json([
    { query: 'wireless headphones', normalizedQuery: 'wireless headphones', searchCount: 450, uniqueUsers: 320, avgResults: 12.5, clickThroughRate: 68, conversions: 18, revenue: 35982 },
    { query: 'gaming mouse', normalizedQuery: 'gaming mouse', searchCount: 380, uniqueUsers: 290, avgResults: 8.2, clickThroughRate: 72, conversions: 22, revenue: 16796 },
    { query: 'laptop pro', normalizedQuery: 'laptop pro', searchCount: 220, uniqueUsers: 180, avgResults: 5.1, clickThroughRate: 55, conversions: 8, revenue: 79920 },
    { query: 'bluetooth speaker', normalizedQuery: 'bluetooth speaker', searchCount: 195, uniqueUsers: 165, avgResults: 15.3, clickThroughRate: 45, conversions: 6, revenue: 2998 },
  ]);
});

app.get('/analytics/geo', (req, res) => {
  res.json([
    { country: 'United States', countryCode: 'US', views: 4500, clicks: 385, conversions: 28, revenue: 53190, commission: 3723 },
    { country: 'United Kingdom', countryCode: 'GB', views: 2100, clicks: 168, conversions: 12, revenue: 22788, commission: 1595 },
    { country: 'Canada', countryCode: 'CA', views: 1800, clicks: 144, conversions: 10, revenue: 18990, commission: 1329 },
    { country: 'Germany', countryCode: 'DE', views: 1500, clicks: 108, conversions: 8, revenue: 15192, commission: 1063 },
    { country: 'Australia', countryCode: 'AU', views: 1200, clicks: 86, conversions: 6, revenue: 11394, commission: 798 },
  ]);
});

// ========== TRACKING API ==========

app.post('/analytics/track/click', (req, res) => {
  const click = { ...req.body, id: Date.now().toString(), createdAt: new Date() };
  res.status(201).json(click);
});

app.post('/analytics/track/conversion', (req, res) => {
  const conversion = { ...req.body, id: Date.now().toString(), createdAt: new Date() };
  res.status(201).json(conversion);
});

// ========== ANALYTICS VISUAL FEATURES ENDPOINTS ==========

// Demographics endpoints
app.get('/analytics/demographics/gender-split', (req, res) => {
  res.json({
    male: { count: 9239, percentage: 58.3 },
    female: { count: 6608, percentage: 41.7 },
    other: { count: 0, percentage: 0 }
  });
});

app.get('/analytics/demographics/age-distribution', (req, res) => {
  res.json([
    { range: '18-24', count: 6022, percentage: 38 },
    { range: '25-34', count: 4437, percentage: 28 },
    { range: '35-44', count: 2853, percentage: 18 },
    { range: '45-54', count: 1585, percentage: 10 },
    { range: '55+', count: 951, percentage: 6 }
  ]);
});

app.get('/analytics/demographics/interests', (req, res) => {
  res.json([
    { name: 'Technology Enthusiasts', count: 18980, percentage: 42 },
    { name: 'Business Professionals', count: 12653, percentage: 28 },
    { name: 'Shopping Lovers', count: 8134, percentage: 18 },
    { name: 'Travel & Tourism', count: 5423, percentage: 12 }
  ]);
});

app.get('/analytics/demographics/new-vs-returning', (req, res) => {
  res.json({
    new: { count: 10234, percentage: 64.2 },
    returning: { count: 5706, percentage: 35.8 }
  });
});

app.get('/analytics/demographics/languages', (req, res) => {
  res.json([
    { code: 'en', count: 12500, percentage: 78 },
    { code: 'es', count: 1900, percentage: 12 },
    { code: 'fr', count: 950, percentage: 6 },
    { code: 'de', count: 640, percentage: 4 }
  ]);
});

// Devices endpoints
app.get('/analytics/devices/breakdown', (req, res) => {
  res.json([
    { type: 'Desktop', count: 32000, percentage: 52 },
    { type: 'Mobile', count: 24615, percentage: 40 },
    { type: 'Tablet', count: 4923, percentage: 8 }
  ]);
});

app.get('/analytics/devices/browsers', (req, res) => {
  res.json([
    { name: 'Chrome', count: 38000, percentage: 62 },
    { name: 'Safari', count: 14700, percentage: 24 },
    { name: 'Firefox', count: 5500, percentage: 9 },
    { name: 'Edge', count: 3077, percentage: 5 }
  ]);
});

app.get('/analytics/devices/operating-systems', (req, res) => {
  res.json([
    { name: 'Windows', count: 28000, percentage: 46 },
    { name: 'macOS', count: 18300, percentage: 30 },
    { name: 'iOS', count: 9200, percentage: 15 },
    { name: 'Android', count: 5500, percentage: 9 }
  ]);
});

// Content endpoints
app.get('/analytics/content/categories', (req, res) => {
  res.json([
    { name: 'Blog', count: 12500, percentage: 40 },
    { name: 'Tools', count: 9375, percentage: 30 },
    { name: 'Services', count: 6250, percentage: 20 },
    { name: 'Product', count: 3125, percentage: 10 }
  ]);
});

app.get('/analytics/content/tags', (req, res) => {
  res.json([
    { name: 'Technology', count: 8500, percentage: 22 },
    { name: 'Reviews', count: 7200, percentage: 19 },
    { name: 'Deals', count: 6500, percentage: 17 },
    { name: 'Tutorials', count: 5800, percentage: 15 },
    { name: 'News', count: 4200, percentage: 11 },
    { name: 'Comparisons', count: 3800, percentage: 10 },
    { name: 'Buying Guides', count: 2300, percentage: 6 }
  ]);
});

app.get('/analytics/content/placements', (req, res) => {
  res.json([
    { type: 'Sidebar', count: 15500, percentage: 35 },
    { type: 'In-Content', count: 13286, percentage: 30 },
    { type: 'Header', count: 8857, percentage: 20 },
    { type: 'Footer', count: 4429, percentage: 10 },
    { type: 'Popup', count: 2214, percentage: 5 }
  ]);
});

app.get('/analytics/content/landing-pages', (req, res) => {
  res.json([
    { url: '/home', visits: 12456, percentage: 25, ctr: 3.2 },
    { url: '/products/summer-sale', visits: 8934, percentage: 18, ctr: 4.5 },
    { url: '/blog/top-10-gadgets', visits: 6721, percentage: 14, ctr: 2.8 },
    { url: '/reviews/best-laptops-2024', visits: 5432, percentage: 11, ctr: 3.9 },
    { url: '/deals', visits: 4210, percentage: 9, ctr: 5.1 },
    { url: '/tools/price-comparison', visits: 3890, percentage: 8, ctr: 4.2 },
    { url: '/guides/buying-guide', visits: 3210, percentage: 7, ctr: 3.7 },
    { url: '/category/electronics', visits: 2890, percentage: 6, ctr: 2.9 },
    { url: '/about', visits: 1021, percentage: 2, ctr: 1.5 }
  ]);
});

// Social sources endpoint
app.get('/analytics/social-sources', (req, res) => {
  res.json([
    { name: 'Facebook', count: 8500, percentage: 42 },
    { name: 'Twitter', count: 5667, percentage: 28 },
    { name: 'Instagram', count: 3643, percentage: 18 },
    { name: 'LinkedIn', count: 1619, percentage: 8 },
    { name: 'Other', count: 810, percentage: 4 }
  ]);
});

// ==========================================
// DEVICE TRACKING ENDPOINTS
// ==========================================

app.get('/analytics/devices/detailed', (req, res) => {
  res.json({
    deviceTypes: [
      { type: 'desktop', count: 32000, percentage: 52 },
      { type: 'mobile', count: 24615, percentage: 40 },
      { type: 'tablet', count: 4923, percentage: 8 },
    ],
    browsers: [
      { name: 'Chrome', count: 38000, percentage: 62, version: '120+' },
      { name: 'Safari', count: 14700, percentage: 24, version: '17+' },
      { name: 'Firefox', count: 5500, percentage: 9, version: '121+' },
      { name: 'Edge', count: 3077, percentage: 5, version: '120+' },
    ],
    operatingSystems: [
      { name: 'Windows', count: 28000, percentage: 46, version: '11' },
      { name: 'macOS', count: 18300, percentage: 30, version: '14' },
      { name: 'iOS', count: 9200, percentage: 15, version: '17' },
      { name: 'Android', count: 5500, percentage: 9, version: '14' },
    ],
    screenResolutions: [
      { resolution: '1920x1080', count: 28000, percentage: 46 },
      { resolution: '2560x1440', count: 12300, percentage: 20 },
      { resolution: '1366x768', count: 8900, percentage: 14.5 },
      { resolution: '390x844', count: 6500, percentage: 10.6 },
      { resolution: '414x896', count: 4300, percentage: 7 },
    ],
    devicePixelRatios: [
      { ratio: '1', count: 28000, percentage: 46 },
      { ratio: '2', count: 24615, percentage: 40 },
      { ratio: '3', count: 8615, percentage: 14 },
    ],
    orientations: [
      { orientation: 'landscape', count: 45000, percentage: 73 },
      { orientation: 'portrait', count: 16538, percentage: 27 },
    ],
    touchCapabilities: [
      { touch: true, count: 34153, percentage: 55.5 },
      { touch: false, count: 27385, percentage: 44.5 },
    ],
    colorSchemes: [
      { scheme: 'light', count: 43000, percentage: 70 },
      { scheme: 'dark', count: 15385, percentage: 25 },
      { scheme: 'no-preference', count: 3153, percentage: 5 },
    ],
    connectionTypes: [
      { type: '4g', count: 42000, percentage: 68.3 },
      { type: 'wifi', count: 12000, percentage: 19.5 },
      { type: '3g', count: 5538, percentage: 9 },
      { type: '2g', count: 2000, percentage: 3.2 },
    ],
    languages: [
      { code: 'en-US', count: 35000, percentage: 57 },
      { code: 'en-GB', count: 8900, percentage: 14.5 },
      { code: 'es-ES', count: 6200, percentage: 10.1 },
      { code: 'fr-FR', count: 4300, percentage: 7 },
      { code: 'de-DE', count: 3153, percentage: 5.1 },
    ],
    timezones: [
      { zone: 'America/New_York', count: 22000, percentage: 35.8 },
      { zone: 'America/Los_Angeles', count: 15000, percentage: 24.4 },
      { zone: 'Europe/London', count: 8900, percentage: 14.5 },
      { zone: 'Europe/Berlin', count: 6200, percentage: 10.1 },
      { zone: 'Asia/Tokyo', count: 4300, percentage: 7 },
    ]
  });
});

// Real-time device tracking
app.post('/analytics/track/device-info', (req, res) => {
  const deviceInfo = req.body;
  console.log('Device info received:', {
    deviceType: deviceInfo.deviceType,
    browser: deviceInfo.browser,
    os: deviceInfo.os,
    screenResolution: deviceInfo.screenResolution,
    timestamp: new Date().toISOString()
  });
  res.json({ received: true, sessionId: deviceInfo.sessionId });
});

// ==========================================
// ADVANCED TRACKING ENDPOINTS
// ==========================================

app.post('/analytics/track/heatmap', (req, res) => {
  res.json({ tracked: req.body.points?.length || 0 });
});

app.get('/analytics/track/heatmap', (req, res) => {
  // Return mock heatmap data
  const points = [];
  for (let i = 0; i < 100; i++) {
    points.push({
      x: Math.floor(Math.random() * 1200),
      y: Math.floor(Math.random() * 800),
      type: Math.random() > 0.5 ? 'click' : 'move',
      count: Math.floor(Math.random() * 50) + 1
    });
  }
  res.json(points);
});

app.post('/analytics/track/engagement', (req, res) => {
  res.json({ received: true });
});

app.get('/analytics/track/engagement/stats', (req, res) => {
  res.json({
    avg_scroll_depth: 67,
    avg_read_time: 245,
    avg_paragraphs: 4.5,
    highlight_rate: 12.5,
    copy_rate: 8.3,
    share_rate: 3.2,
    total_sessions: 15234
  });
});

app.post('/analytics/track/social-share', (req, res) => {
  res.json({ tracked: true });
});

app.get('/analytics/track/social-share/stats', (req, res) => {
  res.json([
    { platform: 'facebook', share_count: 5234, return_count: 890 },
    { platform: 'twitter', share_count: 3421, return_count: 456 },
    { platform: 'pinterest', share_count: 2156, return_count: 678 },
    { platform: 'linkedin', share_count: 890, return_count: 123 },
    { platform: 'email', share_count: 1567, return_count: 234 }
  ]);
});

app.post('/analytics/track/form', (req, res) => {
  res.json({ tracked: true });
});

app.get('/analytics/track/form/analytics', (req, res) => {
  res.json([
    { 
      form_id: 'newsletter',
      form_name: 'Newsletter Signup',
      total_interactions: 3421,
      completion_rate: 45.6,
      avg_time_to_start: 12,
      avg_completion_time: 45,
      top_abandon_field: 'email'
    },
    { 
      form_id: 'contact',
      form_name: 'Contact Form',
      total_interactions: 1234,
      completion_rate: 67.8,
      avg_time_to_start: 8,
      avg_completion_time: 120,
      top_abandon_field: 'message'
    }
  ]);
});

app.post('/analytics/track/price', (req, res) => {
  res.json({ recorded: true });
});

app.get('/analytics/track/price/history', (req, res) => {
  const productId = req.query.productId;
  res.json([
    { platform: 'amazon', price: 29999, original_price: 34999, stock_status: 'in_stock', checked_at: new Date(Date.now() - 86400000) },
    { platform: 'amazon', price: 29999, original_price: 34999, stock_status: 'in_stock', checked_at: new Date(Date.now() - 172800000) },
    { platform: 'amazon', price: 31999, original_price: 34999, stock_status: 'in_stock', checked_at: new Date(Date.now() - 259200000) },
    { platform: 'amazon', price: 27999, original_price: 34999, stock_status: 'low_stock', checked_at: new Date(Date.now() - 345600000) },
  ]);
});

app.post('/analytics/track/search-query', (req, res) => {
  res.json({ tracked: true });
});

app.get('/analytics/track/search-queries', (req, res) => {
  res.json([
    { query: 'best wireless headphones 2024', normalized_query: 'best wireless headphones 2024', search_engine: 'google', search_intent: 'transactional', total_searches: 3421, total_visitors: 2890 },
    { query: 'laptop reviews', normalized_query: 'laptop reviews', search_engine: 'google', search_intent: 'informational', total_searches: 2156, total_visitors: 1890 },
    { query: 'smart watch deals', normalized_query: 'smart watch deals', search_engine: 'bing', search_intent: 'transactional', total_searches: 1890, total_visitors: 1567 },
    { query: 'gaming mouse recommendations', normalized_query: 'gaming mouse recommendations', search_engine: 'google', search_intent: 'informational', total_searches: 1234, total_visitors: 1123 },
    { query: 'bluetooth speaker sale', normalized_query: 'bluetooth speaker sale', search_engine: 'google', search_intent: 'transactional', total_searches: 987, total_visitors: 890 }
  ]);
});

app.get('/analytics/track/search-intent', (req, res) => {
  res.json([
    { search_intent: 'transactional', count: 8234, percentage: 62.5 },
    { search_intent: 'informational', count: 4567, percentage: 34.7 },
    { search_intent: 'navigational', count: 369, percentage: 2.8 }
  ]);
});

app.post('/analytics/track/time-on-page', (req, res) => {
  res.json({ received: true });
});

// Dashboard consolidated endpoint
app.get('/analytics/dashboard', (req, res) => {
  const { tab = 'overview', range = '7d' } = req.query;
  
  res.json({
    kpis: {
      revenue: { value: '$24,563', change: 12.5, sparkline: [20, 35, 30, 45, 40, 55, 60] },
      clicks: { value: '15,847', change: 8.2, sparkline: [30, 40, 35, 50, 45, 60, 65] },
      conversionRate: { value: '3.24%', change: -2.1, sparkline: [4, 3.5, 3.8, 3.2, 3.5, 3.1, 3.24] },
      visitors: { value: '45,231', change: 15.3, sparkline: [35, 40, 38, 50, 48, 60, 65] },
      sales: { value: '1,247', change: 10.8, sparkline: [25, 30, 28, 40, 38, 50, 55] },
      pageviews: { value: '128,456', change: 18.7, sparkline: [40, 50, 45, 60, 55, 70, 75] }
    },
    genderSplit: {
      male: { count: 9239, percentage: 58.3 },
      female: { count: 6608, percentage: 41.7 }
    },
    newVsReturning: {
      new: { count: 10234, percentage: 64.2 },
      returning: { count: 5706, percentage: 35.8 }
    },
    ageDistribution: [
      { range: '18-24', count: 6022, percentage: 38 },
      { range: '25-34', count: 4437, percentage: 28 },
      { range: '35-44', count: 2853, percentage: 18 },
      { range: '45-54', count: 1585, percentage: 10 },
      { range: '55+', count: 951, percentage: 6 }
    ],
    interests: [
      { name: 'Technology Enthusiasts', count: 18980, percentage: 42 },
      { name: 'Business Professionals', count: 12653, percentage: 28 },
      { name: 'Shopping Lovers', count: 8134, percentage: 18 },
      { name: 'Travel & Tourism', count: 5423, percentage: 12 }
    ],
    devices: [
      { type: 'Desktop', count: 32000, percentage: 52 },
      { type: 'Mobile', count: 24615, percentage: 40 },
      { type: 'Tablet', count: 4923, percentage: 8 }
    ],
    countries: [
      { code: 'us', name: 'United States', count: 27692, percentage: 45 },
      { code: 'gb', name: 'United Kingdom', count: 11077, percentage: 18 },
      { code: 'ca', name: 'Canada', count: 7385, percentage: 12 },
      { code: 'au', name: 'Australia', count: 6154, percentage: 10 },
      { code: 'de', name: 'Germany', count: 4923, percentage: 8 },
      { code: 'in', name: 'India', count: 4308, percentage: 7 }
    ],
    socialSources: [
      { name: 'Facebook', count: 8500, percentage: 42 },
      { name: 'Twitter', count: 5667, percentage: 28 },
      { name: 'Instagram', count: 3643, percentage: 18 },
      { name: 'LinkedIn', count: 1619, percentage: 8 },
      { name: 'Other', count: 810, percentage: 4 }
    ],
    categories: [
      { name: 'Blog', count: 12500, percentage: 40 },
      { name: 'Tools', count: 9375, percentage: 30 },
      { name: 'Services', count: 6250, percentage: 20 },
      { name: 'Product', count: 3125, percentage: 10 }
    ],
    placements: [
      { type: 'Sidebar', count: 15500, percentage: 35 },
      { type: 'In-Content', count: 13286, percentage: 30 },
      { type: 'Header', count: 8857, percentage: 20 },
      { type: 'Footer', count: 4429, percentage: 10 },
      { type: 'Popup', count: 2214, percentage: 5 }
    ],
    landingPages: [
      { url: '/home', visits: 12456, percentage: 25, ctr: 3.2 },
      { url: '/products/summer-sale', visits: 8934, percentage: 18, ctr: 4.5 },
      { url: '/blog/top-10-gadgets', visits: 6721, percentage: 14, ctr: 2.8 },
      { url: '/reviews/best-laptops-2024', visits: 5432, percentage: 11, ctr: 3.9 },
      { url: '/deals', visits: 4210, percentage: 9, ctr: 5.1 }
    ]
  });
});

// Root endpoint
app.get('/', (req, res) => {
  res.json({ 
    message: 'Affiliate API Server',
    version: '1.0.0',
    endpoints: {
      health: '/api/v1/health',
      products: ['/products', '/products/stats'],
      users: ['/users', '/users/stats'],
      ribbons: ['/ribbons', '/ribbons/active'],
      tags: ['/tags', '/tags/active'],
      media: ['/media', '/media/stats'],
      analytics: {
        dashboard: '/analytics/dashboard',
        realtime: '/analytics/realtime',
        revenue: '/analytics/revenue',
        commissions: '/analytics/commissions',
        topEarners: '/analytics/top-earners',
        topProducts: '/analytics/top-products',
        funnel: '/analytics/funnel',
        campaigns: '/analytics/campaigns',
        devices: '/analytics/devices',
        sources: '/analytics/sources',
        geo: '/analytics/geo',
        searchTerms: '/analytics/search-terms',
        demographics: {
          genderSplit: '/analytics/demographics/gender-split',
          ageDistribution: '/analytics/demographics/age-distribution',
          interests: '/analytics/demographics/interests',
          newVsReturning: '/analytics/demographics/new-vs-returning',
          languages: '/analytics/demographics/languages',
        },
        content: {
          categories: '/analytics/content/categories',
          tags: '/analytics/content/tags',
          placements: '/analytics/content/placements',
          landingPages: '/analytics/content/landing-pages',
        },
        socialSources: '/analytics/social-sources',
        advanced: {
          heatmap: '/analytics/track/heatmap',
          engagement: '/analytics/track/engagement/stats',
          socialShare: '/analytics/track/social-share/stats',
          formAnalytics: '/analytics/track/form/analytics',
          priceHistory: '/analytics/track/price/history',
          searchQueries: '/analytics/track/search-queries',
          searchIntent: '/analytics/track/search-intent',
        },
      }
    }
  });
});

app.listen(PORT, () => {
  console.log(`🚀 API Server running on http://localhost:${PORT}`);
  console.log(`📡 Health:  http://localhost:${PORT}/api/v1/health`);
  console.log(`📦 Products: http://localhost:${PORT}/products`);
  console.log(`👥 Users:   http://localhost:${PORT}/users`);
  console.log(`🏷️  Tags:    http://localhost:${PORT}/tags`);
  console.log(`🎀 Ribbons: http://localhost:${PORT}/ribbons`);
  console.log(`🖼️  Media:   http://localhost:${PORT}/media`);
  console.log(`📊 Analytics: http://localhost:${PORT}/analytics/dashboard`);
  console.log(`💰 Revenue: http://localhost:${PORT}/analytics/revenue`);
  console.log(`🎯 Funnel:  http://localhost:${PORT}/analytics/funnel`);
});
