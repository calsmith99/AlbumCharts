import React from 'react';
import { Head, Link } from '@inertiajs/react';

export default function Show({ chart }) {
    return (
        <div className="max-w-5xl mx-auto py-10">
            <Head title={`Chart - ${chart.chart_type} (${chart.week_start_date})`} />
            <h2 className="text-2xl font-bold mb-4">
                {chart.chart_type} Chart - Week of {new Date(chart.week_start_date).toLocaleDateString()}
            </h2>
            <p className="mb-2 text-gray-600">Imported by: {chart.user?.name || chart.user?.email}</p>
            <div className="grid grid-cols-5 grid-rows-5 gap-2 aspect-square">
                {chart.chart_entries.map((entry, idx) => {
                    // Use album image URL if available, fallback to gray
                    const imageUrl = entry.album.image_url || 'https://via.placeholder.com/300x300?text=No+Art';
                    return (
                        <div
                            key={entry.id}
                            className="relative flex items-end justify-center h-32 w-32 rounded overflow-hidden shadow-md border"
                            style={{
                                backgroundImage: `url('${imageUrl}')`,
                                backgroundSize: 'cover',
                                backgroundPosition: 'center',
                            }}
                        >
                            <div className="absolute inset-0 bg-black bg-opacity-30"></div>
                            <div className="relative z-10 text-white text-xs font-semibold text-center p-2 w-full truncate">
                                {entry.album.name}
                                <br />
                                <span className="text-xs font-normal">{entry.album.artist.name}</span>
                            </div>
                        </div>
                    );
                })}
            </div>
            <div className="mt-8">
                <Link href="/dashboard" className="text-blue-600 hover:underline">← Back to Dashboard</Link>
            </div>
        </div>
    );
}
