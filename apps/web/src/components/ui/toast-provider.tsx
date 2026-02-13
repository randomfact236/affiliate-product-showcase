"use client"

import { Toaster } from "sonner"

export function ToastProvider() {
  return (
    <Toaster 
      position="top-right"
      toastOptions={{
        style: {
          background: "white",
          border: "1px solid #e5e7eb",
          padding: "16px",
          borderRadius: "8px",
          boxShadow: "0 4px 6px -1px rgba(0, 0, 0, 0.1)",
        },
      }}
    />
  )
}
