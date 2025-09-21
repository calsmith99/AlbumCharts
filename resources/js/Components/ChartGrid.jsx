import React from 'react';
import { useState } from 'react';
import AlbumTile from './AlbumTile';
import TrackTimelineModal from './TrackTimelineModal';

export default function ChartGrid({ chart, gridSize = 5 }) {
    const [modalOpen, setModalOpen] = useState(false);
    const [modalAlbum, setModalAlbum] = useState(null);
    const [modalTracks, setModalTracks] = useState([]);

    if (!chart || !chart.chart_entries) return null;

    // Dummy: expects entry.album.tracks to be [{name, playedAt: [timestamp, ...]}]
    // Replace with real data from backend/API as needed

    const handleAlbumClick = (entry) => {
        setModalAlbum(entry.album);
        fetch(`/albums/${entry.album.id}/track-listens`)
            .then(res => res.json())
            .then(listens => {
                // Group listens by track name
                const trackMap = {};
                listens.forEach(listen => {
                    if (!trackMap[listen.track_name]) trackMap[listen.track_name] = [];
                    trackMap[listen.track_name].push(listen.listened_at);
                });
                const tracks = Object.keys(trackMap).map(name => ({ name, playedAt: trackMap[name] }));
                setModalTracks(tracks);
                setModalOpen(true);
            });
    };

    // Ensure we always render gridSize*gridSize tiles. Fill missing entries with null placeholders.
    const total = gridSize * gridSize;
    const entries = Array.from({ length: total }).map((_, i) => chart.chart_entries[i] || null);

    return (
        <div className="mt-8 mx-auto max-w-6xl">
            <h2 className="text-2xl font-bold mb-4 text-center">
                {chart.chart_type} Chart - Week of {new Date(chart.week_start_date).toLocaleDateString()}
            </h2>
            <div className={`grid gap-2 aspect-square`} style={{ gridTemplateColumns: `repeat(${gridSize}, minmax(0,1fr))`, gridTemplateRows: `repeat(${gridSize}, minmax(0,1fr))` }}>
                {entries.map((entry, idx) => (
                    entry ? (
                        <div key={entry.id} onClick={() => handleAlbumClick(entry)} className="cursor-pointer">
                            <AlbumTile
                                album={entry.album.name}
                                artist={entry.album.artist.name}
                                imageUrl={entry.album.image_url || 'https://via.placeholder.com/300x300?text=No+Art'}
                                completed={entry.completed_album}
                            />
                        </div>
                    ) : (
                        <div key={`empty-${idx}`} className="h-32 w-32 bg-gray-200 rounded border" />
                    )
                ))}
            </div>
            <TrackTimelineModal
                show={modalOpen}
                onClose={() => setModalOpen(false)}
                album={modalAlbum || {}}
                tracks={modalTracks}
            />
        </div>
    );
}
