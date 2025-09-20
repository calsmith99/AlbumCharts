import React from 'react';
import { Head } from '@inertiajs/react';

export default function Layout({ children, title }) {
    return (
        <>
            <Head title={title} />
            <div className="min-h-screen bg-gray-100">
                <nav className="bg-white shadow">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="flex justify-between h-16">
                            <div className="flex">
                                <div className="flex-shrink-0 flex items-center">
                                    <h1 className="text-xl font-bold text-gray-900">
                                        AlbumCharts
                                    </h1>
                                </div>
                            </div>
                            <div className="flex items-center space-x-4">
                                <a
                                    href="/charts"
                                    className="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium"
                                >
                                    My Charts
                                </a>
                                <a
                                    href="/profile"
                                    className="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium"
                                >
                                    Profile
                                </a>
                            </div>
                        </div>
                    </div>
                </nav>
                <main className="py-10">
                    <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                        {children}
                    </div>
                </main>
            </div>
        </>
    );
}