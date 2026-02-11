"use client"

import { useState, useEffect, useCallback } from "react"
import {
  getSettings,
  getSettingValue,
  getDontMissConfig,
  updateSetting,
  bulkUpdateSettings,
  updateDontMissConfig,
  type Setting,
  type DontMissConfig,
} from "@/lib/api/settings"

export function useSettings(group?: string) {
  const [settings, setSettings] = useState<Setting[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    const fetchSettings = async () => {
      try {
        setLoading(true)
        const data = await getSettings(group)
        setSettings(data)
      } catch (err) {
        setError(err instanceof Error ? err.message : "Failed to fetch settings")
      } finally {
        setLoading(false)
      }
    }

    fetchSettings()
  }, [group])

  const update = useCallback(async (key: string, value: any) => {
    try {
      const updated = await updateSetting(key, value)
      setSettings((prev) =>
        prev.map((s) => (s.key === key ? updated : s))
      )
      return updated
    } catch (err) {
      throw err
    }
  }, [])

  const bulkUpdate = useCallback(async (updates: Record<string, any>) => {
    try {
      const result = await bulkUpdateSettings(updates)
      // Refresh settings
      const data = await getSettings(group)
      setSettings(data)
      return result
    } catch (err) {
      throw err
    }
  }, [group])

  return { settings, loading, error, update, bulkUpdate }
}

export function useSetting<T = any>(key: string, defaultValue?: T) {
  const [value, setValue] = useState<T | undefined>(defaultValue)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    const fetchValue = async () => {
      try {
        setLoading(true)
        const data = await getSettingValue<T>(key, defaultValue)
        setValue(data)
      } catch (err) {
        setError(err instanceof Error ? err.message : "Failed to fetch setting")
      } finally {
        setLoading(false)
      }
    }

    fetchValue()
  }, [key, defaultValue])

  const update = useCallback(async (newValue: T) => {
    try {
      await updateSetting(key, newValue)
      setValue(newValue)
    } catch (err) {
      throw err
    }
  }, [key])

  return { value, loading, error, update }
}

export function useDontMissConfig() {
  const [config, setConfig] = useState<DontMissConfig | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    const fetchConfig = async () => {
      try {
        setLoading(true)
        const data = await getDontMissConfig()
        setConfig(data)
      } catch (err) {
        setError(err instanceof Error ? err.message : "Failed to fetch config")
      } finally {
        setLoading(false)
      }
    }

    fetchConfig()
  }, [])

  const update = useCallback(async (updates: Partial<DontMissConfig>) => {
    try {
      await updateDontMissConfig(updates)
      setConfig((prev) => (prev ? { ...prev, ...updates } : null))
    } catch (err) {
      throw err
    }
  }, [])

  return { config, loading, error, update }
}
