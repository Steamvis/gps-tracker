import { afterEach, describe, expect, it, vi } from 'vitest'
import { getServerInfo, type ServerInfo } from './client'

afterEach(() => {
  vi.restoreAllMocks()
})

describe('getServerInfo', () => {
  it('GETs /api/v1/server-info and parses the JSON body', async () => {
    const payload: ServerInfo = {
      app: 'gps-tracker',
      version: 'dev',
      time: '2026-06-20T10:00:00Z',
      postgis: '3.4 USE_GEOS=1',
    }
    const fetchMock = vi.fn().mockResolvedValue({
      ok: true,
      status: 200,
      json: async () => payload,
    })
    vi.stubGlobal('fetch', fetchMock)

    const result = await getServerInfo()

    expect(fetchMock).toHaveBeenCalledWith('/api/v1/server-info')
    expect(result).toEqual(payload)
  })

  it('throws when the response is not ok', async () => {
    const fetchMock = vi.fn().mockResolvedValue({
      ok: false,
      status: 503,
      json: async () => ({}),
    })
    vi.stubGlobal('fetch', fetchMock)

    await expect(getServerInfo()).rejects.toThrow('server-info request failed: 503')
  })
})
