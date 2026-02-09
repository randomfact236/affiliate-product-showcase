import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  // Ensure development server runs properly
  devIndicators: {
    position: "bottom-right",
  },
  // Allow all hosts for development
  allowedDevOrigins: ["localhost:3000", "127.0.0.1:3000"],
};

export default nextConfig;
