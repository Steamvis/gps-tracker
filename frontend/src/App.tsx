import { ServerInfoPanel } from './features/serverInfo/ServerInfoPanel'

export default function App() {
  return (
    <main className="mx-auto max-w-xl px-4 py-12">
      <h1 className="mb-6 text-2xl font-semibold text-slate-900">GPS Tracker</h1>
      <ServerInfoPanel />
    </main>
  )
}
