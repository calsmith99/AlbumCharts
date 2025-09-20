import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import ChartGrid from '@/Components/ChartGrid';

export default function Dashboard({ auth, charts = [], debug = {}, flash = {} }) {
    const user = auth.user;
    const [importing, setImporting] = React.useState(false);

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Dashboard
                </h2>
            }
        >
            <Head title="Dashboard" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="flex flex-col md:flex-row gap-8">
                        {/* Left: Most Recent Chart (60%) */}
                        <div className="md:w-3/5 w-full">
                            {charts.length > 0 && (
                                <div className="bg-white rounded-xl shadow-lg p-8 w-full text-center">
                                    <h3 className="text-2xl font-bold text-gray-900 mb-4">Most Recent Chart</h3>
                                    <ChartGrid chart={charts[0]} />
                                    <Link
                                        href={`/charts/${charts[0].id}`}
                                        className="inline-block bg-blue-600 text-white px-5 py-2 rounded-md font-medium hover:bg-blue-500 transition mt-6"
                                    >
                                        View Full Chart
                                    </Link>
                                </div>
                            )}
                        </div>
                        {/* Right: Other Content (40%) */}
                        <div className="md:w-2/5 w-full space-y-6">
                            {/* Flash Messages */}
                            {(flash.success || flash.error) && (
                        <div className={`rounded-md p-4 ${flash.success ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'}`}>
                            <div className="flex">
                                <div className="flex-shrink-0">
                                    {flash.success ? (
                                        <svg className="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                                        </svg>
                                    ) : (
                                        <svg className="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
                                        </svg>
                                    )}
                                </div>
                                <div className="ml-3">
                                    <p className={`text-sm font-medium ${flash.success ? 'text-green-800' : 'text-red-800'}`}>
                                        {flash.success || flash.error}
                                    </p>
                                </div>
                            </div>
                        </div>
                    )}
                    {/* Last.fm Connection Status */}
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            {user.lastfm_username ? (
                                <div className="flex items-center justify-between">
                                    <div>
                                        <h3 className="text-lg font-medium text-gray-900">
                                            Connected to Last.fm
                                        </h3>
                                        <p className="text-gray-600">
                                            Username: <span className="font-medium">{user.lastfm_username}</span>
                                        </p>
                                        {user.lastfm_connected_at && (
                                            <p className="text-sm text-gray-500">
                                                Connected: {new Date(user.lastfm_connected_at).toLocaleDateString()}
                                            </p>
                                        )}
                                    </div>
                                    <div className="flex space-x-3">
                                        <Link
                                            href={route('lastfm.import')}
                                            method="post"
                                            as="button"
                                            className={`bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-500 disabled:opacity-50 flex items-center justify-center gap-2`}
                                            preserveState
                                            preserveScroll
                                            only={['charts', 'flash']}
                                            disabled={importing}
                                            onClick={() => setImporting(true)}
                                        >
                                            {importing && (
                                                <svg className="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                                </svg>
                                            )}
                                            {importing ? 'Importing' : 'Import New Data'}
                                        </Link>
                                        <Link
                                            href={route('lastfm.disconnect')}
                                            method="post"
                                            as="button"
                                            className="bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-500"
                                        >
                                            Disconnect
                                        </Link>
                                    </div>
                                </div>
                            ) : (
                                <div className="text-center py-8">
                                    <h3 className="text-lg font-medium text-gray-900 mb-2">
                                        Connect Your Last.fm Account
                                    </h3>
                                    <p className="text-gray-600 mb-4">
                                        Connect your Last.fm account to start creating album charts from your listening history.
                                    </p>
                                    <Link
                                        href={route('lastfm.connect')}
                                        className="bg-red-600 text-white px-6 py-3 rounded-md font-medium hover:bg-red-500"
                                    >
                                        Connect Last.fm
                                    </Link>
                                </div>
                            )}
                        </div>
                    </div>

                            {/* Charts Section */}
                            {user.lastfm_username && (
                                <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                                    <div className="p-6">
                                        <div className="flex items-center justify-between mb-6">
                                            <h3 className="text-lg font-medium text-gray-900">
                                                Your Charts
                                            </h3>
                                            <Link
                                                href="/charts/create"
                                                className="bg-green-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-green-500"
                                            >
                                                Create New Chart
                                            </Link>
                                        </div>

                                        {charts.length > 0 ? (
                                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                {charts.map((chart) => (
                                                    <div key={chart.id} className="border rounded-lg p-4 hover:shadow-md transition-shadow flex flex-col">
                                                        <h4 className="font-medium text-gray-900 mb-2">
                                                            {chart.chart_type} Chart - Week of {new Date(chart.week_start_date).toLocaleDateString()}
                                                        </h4>
                                                        <p className="text-gray-600 text-sm mb-3">
                                                            {chart.chart_entries_count} albums
                                                        </p>
                                                        <div className="flex gap-2 mt-auto">
                                                            <Link
                                                                href={`/charts/${chart.id}`}
                                                                className="text-blue-600 hover:text-blue-500 text-sm font-medium"
                                                            >
                                                                View Chart →
                                                            </Link>
                                                            <Link
                                                                href={route('charts.destroy', chart.id)}
                                                                method="delete"
                                                                as="button"
                                                                className="text-red-600 hover:text-red-500 text-sm font-medium border border-red-200 rounded px-2 py-1"
                                                                preserveScroll
                                                                preserveState
                                                            >
                                                                Delete
                                                            </Link>
                                                        </div>
                                                    </div>
                                                ))}
                                            </div>
                                        ) : (
                                            <div className="text-center py-8 text-gray-500">
                                                <p>No charts yet. Import your Last.fm data to get started!</p>
                                            </div>
                                        )}
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
