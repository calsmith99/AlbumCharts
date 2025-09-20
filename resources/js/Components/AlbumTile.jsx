import React from 'react';

export default function AlbumTile({ album, artist, imageUrl, completed }) {
    return (
        <div
            className="relative flex items-end justify-center h-32 w-32 rounded overflow-hidden shadow-md border"
            style={{
                backgroundImage: `url('${imageUrl}')`,
                backgroundSize: 'cover',
                backgroundPosition: 'center',
            }}
        >
            <div className="absolute inset-0 bg-black bg-opacity-30"></div>
            {completed && (
                <div className="absolute top-2 right-2 z-20">
                    <span className="inline-flex items-center px-2 py-1 text-xs font-bold bg-green-600 text-white rounded shadow">Full Play</span>
                </div>
            )}
            <div className="relative z-10 text-white text-xs font-semibold text-center p-2 w-full truncate">
                {album}
                <br />
                <span className="text-xs font-normal">{artist}</span>
            </div>
        </div>
    );
}
