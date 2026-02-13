// CSV Export/Import Utilities

export interface CsvOptions {
  delimiter?: string
  includeHeaders?: boolean
}

export function exportToCSV<T extends Record<string, any>>(
  data: T[],
  columns: { key: keyof T; label: string }[],
  filename: string,
  options: CsvOptions = {}
): void {
  const { delimiter = ",", includeHeaders = true } = options

  const escapeCsv = (value: any): string => {
    const str = String(value ?? "")
    if (str.includes(delimiter) || str.includes('"') || str.includes("\n")) {
      return `"${str.replace(/"/g, '""')}"`
    }
    return str
  }

  const lines: string[] = []

  // Headers
  if (includeHeaders) {
    lines.push(columns.map((col) => escapeCsv(col.label)).join(delimiter))
  }

  // Data rows
  data.forEach((row) => {
    const values = columns.map((col) => escapeCsv(row[col.key]))
    lines.push(values.join(delimiter))
  })

  const csv = lines.join("\n")
  const blob = new Blob(["\ufeff" + csv], { type: "text/csv;charset=utf-8;" })
  const url = URL.createObjectURL(blob)
  
  const link = document.createElement("a")
  link.href = url
  link.download = filename.endsWith(".csv") ? filename : `${filename}.csv`
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  URL.revokeObjectURL(url)
}

export function parseCSV<T extends Record<string, any>>(
  csvText: string,
  columnMapping: { csvHeader: string; key: keyof T; transform?: (value: string) => any }[],
  options: CsvOptions = {}
): T[] {
  const { delimiter = "," } = options
  
  const lines = csvText.trim().split("\n")
  if (lines.length < 2) return []

  const headers = parseCsvLine(lines[0], delimiter)
  const result: T[] = []

  for (let i = 1; i < lines.length; i++) {
    const values = parseCsvLine(lines[i], delimiter)
    const row: any = {}

    columnMapping.forEach((mapping) => {
      const columnIndex = headers.indexOf(mapping.csvHeader)
      if (columnIndex !== -1) {
        const rawValue = values[columnIndex]
        row[mapping.key] = mapping.transform ? mapping.transform(rawValue) : rawValue
      }
    })

    result.push(row as T)
  }

  return result
}

function parseCsvLine(line: string, delimiter: string): string[] {
  const result: string[] = []
  let current = ""
  let inQuotes = false

  for (let i = 0; i < line.length; i++) {
    const char = line[i]
    const nextChar = line[i + 1]

    if (char === '"') {
      if (inQuotes && nextChar === '"') {
        current += '"'
        i++ // Skip next quote
      } else {
        inQuotes = !inQuotes
      }
    } else if (char === delimiter && !inQuotes) {
      result.push(current.trim())
      current = ""
    } else {
      current += char
    }
  }

  result.push(current.trim())
  return result
}

export function downloadTemplate(columns: string[], filename: string): void {
  const csv = columns.join(",")
  const blob = new Blob(["\ufeff" + csv], { type: "text/csv;charset=utf-8;" })
  const url = URL.createObjectURL(blob)
  
  const link = document.createElement("a")
  link.href = url
  link.download = filename
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  URL.revokeObjectURL(url)
}
