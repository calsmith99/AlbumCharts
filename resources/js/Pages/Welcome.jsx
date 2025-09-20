import React from 'react';
import { Head, Link } from '@inertiajs/react';
import ApplicationLogo from '@/Components/ApplicationLogo';
import ChartGrid from '@/Components/ChartGrid';

export default function Welcome({ auth, chart }) {
    return (
        <>
            <Head title="Welcome" />
            <div className="min-h-screen bg-gray-100">
                <nav className="bg-white shadow">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="flex justify-between h-16">
                            <div className="flex">
                                <div className="flex-shrink-0 flex items-center">
                                    <ApplicationLogo className="block h-9 w-auto" />
                                    <h1 className="ml-3 text-xl font-bold text-gray-900">
                                        AlbumCharts
                                    </h1>
                                </div>
                            </div>
                            <div className="flex items-center space-x-4">
                                {auth.user ? (
                                    <>
                                        <Link
                                            href={route('dashboard')}
                                            className="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium"
                                        >
                                            Dashboard
                                        </Link>
                                    </>
                                ) : (
                                    <>
                                        <Link
                                            href={route('login')}
                                            className="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium"
                                        >
                                            Log in
                                        </Link>
                                        <Link
                                            href={route('register')}
                                            className="bg-red-600 text-white hover:bg-red-500 px-3 py-2 rounded-md text-sm font-medium"
                                        >
                                            Register
                                        </Link>
                                    </>
                                )}
                            </div>
                        </div>
                    </div>
                </nav>

                <main className="py-16">
                    <div className="relative overflow-hidden bg-white py-16">
                        <div className="mx-auto max-w-7xl px-6 lg:px-8">
                            <div className="mx-auto max-w-2xl text-center">
                                <h1 className="text-4xl font-bold tracking-tight text-gray-900 sm:text-6xl">
                                    AlbumCharts
                                </h1>
                                <p className="mt-6 text-lg leading-8 text-gray-600">
                                    Create beautiful music charts from your Last.fm listening history. 
                                    Build and share your personal album rankings in a topsters-style grid.
                                </p>
                                <div className="mt-10 flex items-center justify-center gap-x-6">
                                    {auth.user ? (
                                        <Link
                                            href={route('dashboard')}
                                            className="rounded-md bg-red-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-500"
                                        >
                                            View My Charts
                                        </Link>
                                    ) : (
                                        <Link
                                            href={route('register')}
                                            className="rounded-md bg-red-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-500"
                                        >
                                            Get Started
                                        </Link>
                                    )}
                                </div>
                            </div>
                        </div>
                        {/* Full Chart Render */}
                        <ChartGrid chart={chart} />
                    </div>
                </main>
            </div>
        </>
    );
}
