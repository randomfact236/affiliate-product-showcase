// Audit Trail System for Enterprise Compliance

export interface AuditEntry {
  id: string
  action: "CREATE" | "UPDATE" | "DELETE" | "BULK_DELETE" | "BULK_UPDATE" | "EXPORT" | "IMPORT"
  entity: string
  entityId?: string
  userId?: string
  userEmail?: string
  changes?: Record<string, { old?: any; new?: any }>
  metadata?: Record<string, any>
  timestamp: string
  ipAddress?: string
}

const STORAGE_KEY = "category_audit_trail"
const MAX_ENTRIES = 1000

export class AuditTrail {
  static log(entry: Omit<AuditEntry, "id" | "timestamp">): void {
    if (typeof window === "undefined") return

    const auditEntry: AuditEntry = {
      ...entry,
      id: `${Date.now()}-${Math.random().toString(36).substr(2, 9)}`,
      timestamp: new Date().toISOString(),
    }

    const existing = AuditTrail.getAll()
    const updated = [auditEntry, ...existing].slice(0, MAX_ENTRIES)
    
    localStorage.setItem(STORAGE_KEY, JSON.stringify(updated))
    
    // Also log to console in development
    if (process.env.NODE_ENV === "development") {
      console.log("[AUDIT]", auditEntry)
    }
  }

  static getAll(): AuditEntry[] {
    if (typeof window === "undefined") return []
    
    const stored = localStorage.getItem(STORAGE_KEY)
    return stored ? JSON.parse(stored) : []
  }

  static getByEntity(entity: string): AuditEntry[] {
    return AuditTrail.getAll().filter((e) => e.entity === entity)
  }

  static getByAction(action: AuditEntry["action"]): AuditEntry[] {
    return AuditTrail.getAll().filter((e) => e.action === action)
  }

  static clear(): void {
    if (typeof window === "undefined") return
    localStorage.removeItem(STORAGE_KEY)
  }

  static exportToCSV(): string {
    const entries = AuditTrail.getAll()
    const headers = ["Timestamp", "Action", "Entity", "Entity ID", "User", "Changes"]
    
    const rows = entries.map((e) => [
      e.timestamp,
      e.action,
      e.entity,
      e.entityId || "",
      e.userEmail || e.userId || "Anonymous",
      e.changes ? JSON.stringify(e.changes) : "",
    ])

    return [headers, ...rows].map((row) => row.join(",")).join("\n")
  }

  static downloadAuditLog(): void {
    const csv = AuditTrail.exportToCSV()
    const blob = new Blob([csv], { type: "text/csv" })
    const url = URL.createObjectURL(blob)
    const a = document.createElement("a")
    a.href = url
    a.download = `audit-log-${new Date().toISOString().split("T")[0]}.csv`
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    URL.revokeObjectURL(url)
  }
}

// Helper to track changes between old and new values
export function trackChanges<T extends Record<string, any>>(
  oldObj: T,
  newObj: T
): Record<string, { old?: any; new?: any }> {
  const changes: Record<string, { old?: any; new?: any }> = {}
  
  const allKeys = new Set([...Object.keys(oldObj), ...Object.keys(newObj)])
  
  allKeys.forEach((key) => {
    if (JSON.stringify(oldObj[key]) !== JSON.stringify(newObj[key])) {
      changes[key] = {
        old: oldObj[key],
        new: newObj[key],
      }
    }
  })
  
  return changes
}
