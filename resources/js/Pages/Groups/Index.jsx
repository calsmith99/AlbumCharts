import React, { useState } from 'react'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import { Head, router } from '@inertiajs/react'

export default function GroupsIndex({ groups, auth }) {
  const [name, setName] = useState('')

  function createGroup(e) {
    e.preventDefault()
    if (!name.trim()) return
    router.post(route('groups.store'), { name })
  }

  return (
    <AuthenticatedLayout
      user={auth.user}
      header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Groups</h2>}
    >
      <Head title="Your Groups" />

      <div className="py-6">
        <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
          <div className="p-6 bg-white rounded shadow">
            <h1 className="text-2xl font-bold mb-4">Your Groups</h1>

            <form onSubmit={createGroup} className="mb-4 flex gap-2">
              <input className="border rounded px-2 py-1" value={name} onChange={e => setName(e.target.value)} placeholder="New group name" />
              <button className="bg-blue-600 text-white px-3 py-1 rounded" type="submit">Create Group</button>
            </form>

            <div className="grid grid-cols-1 gap-3">
              {groups.map(g => (
                <div key={g.id} className="p-3 border rounded flex justify-between items-center">
                  <div>
                    <div className="font-semibold">{g.name}</div>
                    <div className="text-sm text-gray-600">{g.users_count} members</div>
                  </div>
                  <div>
                    <a href={route('groups.show', g.id)} className="text-blue-600">Open</a>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  )
}
