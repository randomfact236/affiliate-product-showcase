import { NextResponse } from "next/server";

export async function GET() {
  const healthCheck = {
    status: "healthy",
    timestamp: new Date().toISOString(),
    version: "1.0.0",
    services: {
      web: "up",
      api: "unknown",
    },
  };

  // Check API health
  try {
    const apiResponse = await fetch("http://localhost:3003/api/v1/health", {
      method: "GET",
      headers: { "Content-Type": "application/json" },
      cache: "no-store",
    });

    if (apiResponse.ok) {
      healthCheck.services.api = "up";
    } else {
      healthCheck.services.api = `error: ${apiResponse.status}`;
      healthCheck.status = "degraded";
    }
  } catch {
    healthCheck.services.api = "down";
    healthCheck.status = "degraded";
  }

  const statusCode = healthCheck.status === "healthy" ? 200 : 503;

  return NextResponse.json(healthCheck, { status: statusCode });
}
