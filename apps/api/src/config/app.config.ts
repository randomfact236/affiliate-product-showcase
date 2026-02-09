import { registerAs } from "@nestjs/config";

export const appConfig = registerAs("app", () => {
  const nodeEnv = process.env.NODE_ENV || "development";
  const apiPort = parseInt(process.env.API_PORT || "3001", 10);

  // Parse allowed origins for CORS
  const allowedOriginsStr = process.env.ALLOWED_ORIGINS;
  let allowedOrigins: string[];

  if (allowedOriginsStr) {
    allowedOrigins = allowedOriginsStr.split(",").map((o) => o.trim());
  } else if (nodeEnv === "development") {
    allowedOrigins = ["http://localhost:3000", "http://localhost:3001"];
  } else {
    throw new Error(
      "FATAL: ALLOWED_ORIGINS environment variable is required in production. " +
        "Example: ALLOWED_ORIGINS=https://example.com,https://admin.example.com",
    );
  }

  // Validate origins format
  for (const origin of allowedOrigins) {
    if (origin === "*") {
      throw new Error(
        'FATAL: ALLOWED_ORIGINS cannot contain "*" when credentials are enabled. ' +
          "This is a security vulnerability. Specify explicit origins.",
      );
    }
    if (!origin.startsWith("http://") && !origin.startsWith("https://")) {
      throw new Error(
        `FATAL: Invalid origin "${origin}" in ALLOWED_ORIGINS. ` +
          "Origins must start with http:// or https://",
      );
    }
  }

  return {
    nodeEnv,
    port: apiPort,
    host: process.env.API_HOST || "0.0.0.0",
    allowedOrigins,
    apiUrl: process.env.API_URL || `http://localhost:${apiPort}`,
    webUrl: process.env.WEB_URL || "http://localhost:3000",
  };
});
