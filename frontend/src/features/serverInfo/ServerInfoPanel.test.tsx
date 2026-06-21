import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { render, screen } from '@testing-library/react'
import type { ReactNode } from 'react'
import { afterEach, describe, expect, it, vi } from 'vitest'
import type { ServerInfo } from '../../api/client'
import { ServerInfoPanel } from './ServerInfoPanel'

vi.mock('../../api/client', () => ({
  getServerInfo: vi.fn(),
}))

import { getServerInfo } from '../../api/client'

const mockedGetServerInfo = vi.mocked(getServerInfo)

function renderWithClient(ui: ReactNode) {
  const client = new QueryClient({
    defaultOptions: { queries: { retry: false } },
  })
  return render(<QueryClientProvider client={client}>{ui}</QueryClientProvider>)
}

afterEach(() => {
  vi.clearAllMocks()
})

describe('ServerInfoPanel', () => {
  it('shows a loading state while the query is pending', () => {
    mockedGetServerInfo.mockReturnValue(new Promise<ServerInfo>(() => {}))
    renderWithClient(<ServerInfoPanel />)
    expect(screen.getByText('Loading server info…')).toBeInTheDocument()
  })

  it('renders app, version, time and postgis once loaded', async () => {
    const info: ServerInfo = {
      app: 'gps-tracker',
      version: 'dev',
      time: '2026-06-20T10:00:00Z',
      postgis: '3.4 USE_GEOS=1',
    }
    mockedGetServerInfo.mockResolvedValue(info)
    renderWithClient(<ServerInfoPanel />)

    expect(await screen.findByText('gps-tracker')).toBeInTheDocument()
    expect(screen.getByText('dev')).toBeInTheDocument()
    expect(screen.getByText('2026-06-20T10:00:00Z')).toBeInTheDocument()
    expect(screen.getByText('3.4 USE_GEOS=1')).toBeInTheDocument()
  })
})
