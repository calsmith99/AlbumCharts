import React, { useState, useEffect, useRef } from 'react';
import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

// Single helper to render each hour row with distributed vertical spacing
function renderHourRow(grouped, day, hour) {
    const leftAlbums = grouped[day].filter(album => album._parsedDate.getHours() === hour && hour % 2 !== 0);
    const rightAlbums = grouped[day].filter(album => album._parsedDate.getHours() === hour && hour % 2 === 0);
    const hasAlbum = leftAlbums.length > 0 || rightAlbums.length > 0;
    const spacing = hasAlbum ? -14 : 1;
    return (
        <div
            key={hour}
            className="flex flex-row items-center"
            style={{ minHeight: hasAlbum ? 48 : 2, marginTop: spacing, marginBottom: spacing }}
        >
            {/* Always render left container */}
            <div className="flex items-center justify-end w-[260px]" style={{ marginRight: 20 }}>
                {leftAlbums.length > 0 ? leftAlbums.map(album => (
                    <>
                        <div key={album.id + '-info'} className="mr-6 text-right" style={{ minWidth: 160, maxWidth: 240, whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>
                            <div className="font-bold text-base text-gray-900" style={{ whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>{album.name}</div>
                            <div className="text-base text-gray-600" style={{ whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>{album.artist_name}</div>
                            <div className="text-sm text-gray-500">{album._parsedDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</div>
                        </div>
                        <img
                            key={album.id + '-img'}
                            src={album.image_url || 'https://via.placeholder.com/100x100?text=No+Art'}
                            alt={album.name}
                            className="rounded object-cover border-2 border-blue-400 shadow-lg"
                            style={{ width: 80, height: 80, aspectRatio: '1 / 1', objectFit: 'cover' }}
                        />
                    </>
                )) : null}
            </div>
            {/* Dot always centered */}
            <div className="w-2 h-2 rounded-full bg-gray-400 mx-auto" />
            {/* Always render right container */}
            <div className="flex items-center justify-start w-[260px]" style={{ marginLeft: 20 }}>
                {rightAlbums.length > 0 ? rightAlbums.map(album => (
                    <>
                        <img
                            key={album.id + '-img'}
                            src={album.image_url || 'https://via.placeholder.com/100x100?text=No+Art'}
                            alt={album.name}
                            className="rounded object-cover border-2 border-blue-400 shadow-lg"
                            style={{ width: 80, height: 80, aspectRatio: '1 / 1', objectFit: 'cover' }}
                        />
                        <div key={album.id + '-info'} className="ml-6" style={{ minWidth: 160, maxWidth: 240, whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>
                            <div className="font-bold text-base text-gray-900" style={{ whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>{album.name}</div>
                            <div className="text-base text-gray-600" style={{ whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>{album.artist_name}</div>
                            <div className="text-sm text-gray-500">{album._parsedDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</div>
                        </div>
                    </>
                )) : null}
            </div>
        </div>
    );
}

export default function AlbumTimeline() {
    const [albums, setAlbums] = useState([]);
    const [loading, setLoading] = useState(false);
    const [hasMore, setHasMore] = useState(true);
    const [page, setPage] = useState(1);
    const loader = useRef(null);

    useEffect(() => {
        if (!hasMore) return;
        setLoading(true);
        fetch(`/api/full-album-listens?page=${page}`)
            .then(res => res.json())
            .then(data => {
                setAlbums(prev => [...prev, ...data.albums]);
                setHasMore(data.hasMore);
                setLoading(false);
            });
    }, [page]);

    useEffect(() => {
        const handleScroll = () => {
            if (!loader.current || loading || !hasMore) return;
            const rect = loader.current.getBoundingClientRect();
            if (rect.top < window.innerHeight) {
                setPage(p => p + 1);
            }
        };
        window.addEventListener('scroll', handleScroll);
        return () => window.removeEventListener('scroll', handleScroll);
    }, [loading, hasMore]);

    // Helper to safely parse date
    const parseDate = (d) => {
        if (!d) return null;
        if (typeof d === 'number') return new Date(d * 1000);
        const dateObj = new Date(d);
        return isNaN(dateObj.getTime()) ? null : dateObj;
    };
    // Group albums by day
    // Use the single helper from above for hour row rendering
    const grouped = albums.reduce((acc, album) => {
        const dateObj = parseDate(album.listened_at);
        const day = dateObj ? dateObj.toLocaleDateString() : 'Invalid date';
        if (!acc[day]) acc[day] = [];
        acc[day].push({ ...album, _parsedDate: dateObj });
        return acc;
    }, {});
    const sortedDays = Object.keys(grouped).sort((a, b) => {
        const da = grouped[a][0]._parsedDate;
        const db = grouped[b][0]._parsedDate;
        return db - da;
    });
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Album Timeline
                </h2>
            }
        >
            <Head title="Album Timeline" />
            <div className="py-12 flex flex-col items-center w-full">
                <div className="flex flex-col gap-12 w-full max-w-3xl items-center">
                    {sortedDays.map(day => (
                        <div key={day} className="w-full flex flex-col items-center mb-12">
                            <h2 className="text-lg font-semibold text-blue-700 mb-4 text-center">
                                {grouped[day][0]._parsedDate
                                    ? grouped[day][0]._parsedDate.toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric' })
                                    : 'Invalid date'}
                            </h2>
                            <div className="flex flex-col items-center">
                                {[...Array(24)].map((_, hour) => (
                                    renderHourRow(grouped, day, hour)
                                ))}
                            </div>
                        </div>
                    ))}
                    {loading && <div className="text-center py-4">Loading...</div>}
                    <div ref={loader}></div>
                    {!hasMore && <div className="text-center py-4 text-gray-500">No more albums.</div>}
                    <div className="text-center mt-8">
                        <Link href="/dashboard" className="text-blue-600 hover:underline">Back to Dashboard</Link>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}