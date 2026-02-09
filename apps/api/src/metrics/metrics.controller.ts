import { Controller, Get, Res } from "@nestjs/common";
import { Response } from "express";
import {
  register,
  collectDefaultMetrics,
  Counter,
  Histogram,
} from "prom-client";

// Collect default Node.js metrics
collectDefaultMetrics({
  prefix: "affiliate_",
});

// Custom metrics
const httpRequestDuration = new Histogram({
  name: "affiliate_http_request_duration_seconds",
  help: "Duration of HTTP requests in seconds",
  labelNames: ["method", "route", "status_code"],
  buckets: [0.01, 0.05, 0.1, 0.5, 1, 2, 5],
});

const httpRequestsTotal = new Counter({
  name: "affiliate_http_requests_total",
  help: "Total number of HTTP requests",
  labelNames: ["method", "route", "status_code"],
});

@Controller("metrics")
export class MetricsController {
  @Get()
  async getMetrics(@Res() res: Response) {
    res.set("Content-Type", register.contentType);
    res.end(await register.metrics());
  }
}

export { httpRequestDuration, httpRequestsTotal };
