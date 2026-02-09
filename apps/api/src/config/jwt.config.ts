import { registerAs } from "@nestjs/config";

export const jwtConfig = registerAs("jwt", () => {
  const secret = process.env.JWT_SECRET;
  const refreshSecret = process.env.JWT_REFRESH_SECRET;

  // Critical validation - fail fast on startup
  if (!secret) {
    throw new Error(
      "FATAL: JWT_SECRET environment variable is not defined. " +
        "Authentication is insecure without a proper JWT secret.",
    );
  }

  if (secret.length < 32) {
    throw new Error(
      `FATAL: JWT_SECRET must be at least 32 characters long. ` +
        `Current length: ${secret.length}. ` +
        "Use a cryptographically secure random string.",
    );
  }

  if (!refreshSecret) {
    throw new Error(
      "FATAL: JWT_REFRESH_SECRET environment variable is not defined.",
    );
  }

  if (refreshSecret === secret) {
    throw new Error(
      "FATAL: JWT_REFRESH_SECRET must be different from JWT_SECRET. " +
        "Using the same secret compromises token rotation security.",
    );
  }

  // Check for weak/default secrets
  const weakSecrets = [
    "secret",
    "jwt_secret",
    "your_secret_here",
    "test",
    "123456",
    "password",
    "admin",
  ];

  const lowerSecret = secret.toLowerCase();
  if (weakSecrets.some((weak) => lowerSecret.includes(weak))) {
    throw new Error(
      "FATAL: JWT_SECRET appears to be a weak/default value. " +
        "Generate a strong secret: openssl rand -base64 64",
    );
  }

  return {
    secret,
    refreshSecret,
    expiresIn: process.env.JWT_ACCESS_EXPIRATION || "15m",
    refreshExpiresIn: process.env.JWT_REFRESH_EXPIRATION || "7d",
    issuer: process.env.JWT_ISSUER || "affiliate-platform",
    audience: process.env.JWT_AUDIENCE || "affiliate-api",
  };
});
