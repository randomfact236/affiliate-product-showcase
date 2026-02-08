/** @type {import('next').NextConfig} */
const nextConfig = {
  reactStrictMode: true,
  swcMinify: true,
  async rewrites() {
    return [
      // Proxy API requests to backend
      {
        source: '/api/:path*',
        destination: 'http://localhost:3003/api/v1/:path*',
      },
      // Admin routes proxy to backend (for admin API or future admin panel)
      {
        source: '/admin/:path*',
        destination: 'http://localhost:3003/api/v1/:path*',
      },
    ];
  },
  async redirects() {
    return [
      // Redirect /admin (root) to API docs for now
      {
        source: '/admin',
        destination: 'http://localhost:3003/api/docs',
        permanent: false,
        basePath: false,
      },
    ];
  },
};

module.exports = nextConfig;
