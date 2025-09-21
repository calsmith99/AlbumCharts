import React from 'react'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import { Head } from '@inertiajs/react'
import ChartGrid from '@/Components/ChartGrid'

export default function GroupShow({ group, grid = null, aggregated_chart = null, auth }) {
  return (
    <AuthenticatedLayout
      user={auth.user}
      header={<h2 className="text-xl font-semibold leading-tight text-gray-800">{group.name}</h2>}
    >
      <Head title={group.name} />

      <div className="py-6">
        <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
          <div className="p-6 bg-white rounded shadow">
            <h1 className="text-2xl font-bold">{group.name}</h1>
            <p className="text-sm mt-2">Members:</p>
            <ul>
              {group.users.map(u => (
                <li key={u.id}>{u.name} ({u.email})</li>
              ))}
            </ul>

            <div className="mt-6">
              <p className="mb-2">Group 9x9 chart</p>
              {aggregated_chart ? (
                <ChartGrid chart={aggregated_chart} gridSize={9} />
              ) : (
                <div className="grid grid-cols-9 gap-2">
                  {grid ? (
                    grid.flat().map((cell, i) => (
                      <div key={i} className="h-16 w-16 bg-gray-200" />
                    ))
                  ) : (
                    Array.from({ length: 81 }).map((_, i) => (
                      <div key={i} className="h-16 w-16 bg-gray-200" />
                    ))
                  )}
                </div>
              )}
            </div>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  )
}
