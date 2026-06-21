export type ServerInfo = {
  app: string
  version: string
  time: string
  postgis: string
}

export async function getServerInfo(): Promise<ServerInfo> {
  const response = await fetch('/api/v1/server-info')
  if (!response.ok) {
    throw new Error(`server-info request failed: ${response.status}`)
  }
  return (await response.json()) as ServerInfo
}
