import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  // Ensure development server runs properly
  devIndicators: {
    position: "bottom-right",
  },
  // Allow all hosts for development
  allowedDevOrigins: ["localhost:3000", "127.0.0.1:3000"],
  // Allow external images
  images: {
    remotePatterns: [
      {
        protocol: "https",
        hostname: "images.unsplash.com",
      },
      {
        protocol: "https",
        hostname: "demo.tagdiv.com",
      },
    ],
    unoptimized: true,
  },
};

export default nextConfig;
