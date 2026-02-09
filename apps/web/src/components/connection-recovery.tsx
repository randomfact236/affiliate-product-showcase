"use client";

import { useEffect, useState } from "react";

export function ConnectionRecovery() {
  const [isOnline, setIsOnline] = useState(true);
  const [showReload, setShowReload] = useState(false);
  const [retryCount, setRetryCount] = useState(0);

  useEffect(() => {
    // Handle online/offline events
    const handleOnline = () => {
      console.log("[ConnectionRecovery] Connection restored");
      setIsOnline(true);
      setShowReload(false);
      
      // Auto-reload after a brief delay to ensure server is ready
      setTimeout(() => {
        window.location.reload();
      }, 1500);
    };

    const handleOffline = () => {
      console.log("[ConnectionRecovery] Connection lost");
      setIsOnline(false);
      setShowReload(true);
    };

    window.addEventListener("online", handleOnline);
    window.addEventListener("offline", handleOffline);

    // Heartbeat check
    const heartbeat = setInterval(async () => {
      try {
        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 5000);
        
        const response = await fetch("/api/health", { 
          method: "HEAD",
          cache: "no-store",
          signal: controller.signal
        });
        
        clearTimeout(timeout);
        
        if (response.ok && !isOnline) {
          handleOnline();
        }
      } catch {
        if (isOnline) {
          setRetryCount((prev) => prev + 1);
          
          // Only show offline after multiple retries
          if (retryCount > 2) {
            handleOffline();
          }
        }
      }
    }, 5000);

    return () => {
      window.removeEventListener("online", handleOnline);
      window.removeEventListener("offline", handleOffline);
      clearInterval(heartbeat);
    };
  }, [isOnline, retryCount]);

  const handleManualReload = () => {
    window.location.reload();
  };

  const handleRetryConnection = async () => {
    setRetryCount(0);
    try {
      const controller = new AbortController();
      const timeout = setTimeout(() => controller.abort(), 5000);
      
      await fetch("/api/health", { 
        method: "HEAD",
        cache: "no-store",
        signal: controller.signal
      });
      
      clearTimeout(timeout);
      window.location.reload();
    } catch {
      // Still offline
    }
  };

  if (!showReload && isOnline) return null;

  return (
    <div className="fixed inset-0 z-[9999] flex items-center justify-center bg-black/80 backdrop-blur-sm">
      <div className="mx-4 max-w-md rounded-xl bg-white p-8 text-center shadow-2xl">
        <div className="mb-4 flex justify-center">
          <div className="flex h-16 w-16 items-center justify-center rounded-full bg-yellow-100">
            <svg
              className="h-8 w-8 text-yellow-600"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
              />
            </svg>
          </div>
        </div>

        <h2 className="mb-2 text-xl font-bold text-gray-900">
          Connection Interrupted
        </h2>
        
        <p className="mb-6 text-gray-600">
          The server connection was lost. This can happen when your computer 
          goes to sleep or the server restarts.
        </p>

        <div className="space-y-3">
          <button
            onClick={handleManualReload}
            className="w-full rounded-lg bg-blue-600 px-6 py-3 font-semibold text-white transition-colors hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
          >
            Reload Page
          </button>
          
          <button
            onClick={handleRetryConnection}
            className="w-full rounded-lg border border-gray-300 bg-white px-6 py-3 font-semibold text-gray-700 transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
          >
            Retry Connection
          </button>
        </div>

        <p className="mt-4 text-xs text-gray-500">
          Auto-reload will occur when connection is restored
        </p>
      </div>
    </div>
  );
}
