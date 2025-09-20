import React from 'react';
import { useState } from 'react';
import AlbumTile from './AlbumTile';
import TrackTimelineModal from './TrackTimelineModal';

export default function ChartGrid({ chart }) {
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

    return (
        <div className="mt-8 mx-auto max-w-4xl">
            <h2 className="text-2xl font-bold mb-4 text-center">
                {chart.chart_type} Chart - Week of {new Date(chart.week_start_date).toLocaleDateString()}
            </h2>
            <div className="grid grid-cols-5 grid-rows-5 gap-2 aspect-square">
                {chart.chart_entries.map((entry) => (
                    <div key={entry.id} onClick={() => handleAlbumClick(entry)} className="cursor-pointer">
                        <AlbumTile
                            album={entry.album.name}
                            artist={entry.album.artist.name}
                            imageUrl={entry.album.image_url || 'https://via.placeholder.com/300x300?text=No+Art'}
                            completed={entry.completed_album}
                        />
                    </div>
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
