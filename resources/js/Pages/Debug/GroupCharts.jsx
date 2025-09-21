import React from 'react'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import ChartGrid from '@/Components/ChartGrid'

export default function GroupCharts({ group, members, aggregated, auth }) {
  return (
    <AuthenticatedLayout user={auth?.user} header={<h2 className="text-xl">Debug Group Charts</h2>}>
      <div className="p-6">
        <h1 className="text-2xl font-bold">Group: {group.name} (id: {group.id})</h1>

        <div className="mt-6 grid grid-cols-1 gap-6">
          {members.map(m => {
            const chart = m.latestChart ?? m.latest_chart ?? null;
            // Some Inertia/Laravel serializations use snake_case for relation keys
            return (
              <div key={m.id} className="p-4 border rounded bg-white">
                <h3 className="font-semibold">Member: {m.name} (id: {m.id})</h3>
                {chart ? (
                  <ChartGrid chart={chart} gridSize={9} />
                ) : (
                  <div className="text-sm text-gray-500">No latest chart</div>
                )}
              </div>
            )
          })}
        </div>

        <div className="mt-8 p-4 border rounded bg-white">
          <h2 className="text-xl font-bold">Aggregated Chart</h2>
          {aggregated ? (
            <ChartGrid chart={aggregated} gridSize={9} />
          ) : (
            <div className="text-sm text-gray-500">No aggregated chart</div>
          )}
        </div>
      </div>
    </AuthenticatedLayout>
  )
}
