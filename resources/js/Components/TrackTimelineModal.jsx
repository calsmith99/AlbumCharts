import React from 'react';
import Modal from './Modal';

export default function TrackTimelineModal({ show, onClose, album, tracks }) {
    // Helper to handle both unix timestamp and ISO string
    const formatDate = (ts) => {
        if (!ts) return 'Invalid date';
        if (typeof ts === 'number') {
            return new Date(ts * 1000).toLocaleString();
        }
        const d = new Date(ts);
        return isNaN(d.getTime()) ? 'Invalid date' : d.toLocaleString();
    };

    // Group track plays by day
    const groupPlaysByDay = (tracks) => {
        const dayMap = {};
        tracks.forEach(track => {
            if (track.playedAt && track.playedAt.length > 0) {
                track.playedAt.forEach(ts => {
                    let dateObj = typeof ts === 'number' ? new Date(ts * 1000) : new Date(ts);
                    if (isNaN(dateObj.getTime())) return;
                    const dayKey = dateObj.toLocaleDateString();
                    const dayOfWeek = dateObj.toLocaleDateString(undefined, { weekday: 'long' });
                    if (!dayMap[dayKey]) dayMap[dayKey] = { dayOfWeek, plays: [] };
                    dayMap[dayKey].plays.push({
                        trackName: track.name,
                        time: dateObj.toLocaleTimeString(),
                        fullDate: dateObj.toLocaleString(),
                        timestamp: dateObj.getTime(),
                    });
                });
            }
        });
        return dayMap;
    };
    // Prepare grouped data
    const dayMap = groupPlaysByDay(tracks);
    // Sort days by date (latest first)
    const sortedDays = Object.keys(dayMap)
        .sort((a, b) => new Date(b) - new Date(a));

    return (
        <Modal show={show} onClose={onClose} maxWidth="md">
            <div className="p-6 max-h-[80vh] overflow-y-auto flex flex-col items-center">
                <h2 className="text-xl font-bold mb-4 text-center">{album.name} - Timeline</h2>
                <div className="space-y-4 w-full max-w-xl">
                    {tracks && tracks.length > 0 ? (
                        sortedDays.length > 0 ? (
                            sortedDays.map(dayKey => {
                                const { dayOfWeek, plays } = dayMap[dayKey];
                                // Sort plays by timestamp (earliest to latest)
                                const sortedPlays = plays.sort((a, b) => a.timestamp - b.timestamp);
                                return (
                                    <div key={dayKey} className="mb-6">
                                        <h3 className="text-lg font-semibold text-blue-700 mb-2">{dayOfWeek}</h3>
                                        <ul className="list-disc ml-4">
                                            {sortedPlays.map((play, idx) => (
                                                <li key={idx}>
                                                    <span className="font-medium text-gray-800">{play.trackName}</span>
                                                    <span className="ml-2 text-xs text-gray-500">{play.time}</span>
                                                </li>
                                            ))}
                                        </ul>
                                    </div>
                                );
                            })
                        ) : (
                            <div className="text-gray-500">No track plays found for this album.</div>
                        )
                    ) : (
                        <div className="text-gray-500">No track data available.</div>
                    )}
                </div>
            </div>
        </Modal>
    );
}
