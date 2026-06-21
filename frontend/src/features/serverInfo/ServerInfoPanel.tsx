import { useQuery } from '@tanstack/react-query'
import { getServerInfo } from '../../api/client'

function Field({ label, value }: { label: string; value: string }) {
  return (
    <div className="flex justify-between gap-4 border-b border-slate-200 py-2 last:border-0">
      <dt className="font-medium text-slate-500">{label}</dt>
      <dd className="font-mono text-slate-900">{value}</dd>
    </div>
  )
}

export function ServerInfoPanel() {
  const { isPending, isError, data, error } = useQuery({
    queryKey: ['server-info'],
    queryFn: getServerInfo,
  })

  if (isPending) {
    return <p className="text-slate-500">Loading server info…</p>
  }

  if (isError) {
    return (
      <p className="text-red-600">
        Failed to load server info: {error instanceof Error ? error.message : 'unknown error'}
      </p>
    )
  }

  return (
    <dl className="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
      <Field label="app" value={data.app} />
      <Field label="version" value={data.version} />
      <Field label="time" value={data.time} />
      <Field label="postgis" value={data.postgis} />
    </dl>
  )
}
