import React, { useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';

export default function Connect({ auth }) {
    const { data, setData, post, processing, errors } = useForm({
        username: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('lastfm.store'));
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Connect Last.fm</h2>}
        >
            <Head title="Connect Last.fm" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <div className="max-w-md mx-auto">
                                <div className="mb-6">
                                    <h3 className="text-lg font-medium text-gray-900 mb-2">
                                        Connect Your Last.fm Account
                                    </h3>
                                    <p className="text-gray-600">
                                        Enter your Last.fm username to start creating charts from your listening history.
                                    </p>
                                </div>

                                <form onSubmit={submit} className="space-y-6">
                                    <div>
                                        <InputLabel htmlFor="username" value="Last.fm Username" />

                                        <TextInput
                                            id="username"
                                            name="username"
                                            value={data.username}
                                            className="mt-1 block w-full"
                                            autoComplete="username"
                                            isFocused={true}
                                            onChange={(e) => setData('username', e.target.value)}
                                            placeholder="Enter your Last.fm username"
                                        />

                                        <InputError message={errors.username} className="mt-2" />
                                    </div>

                                    <div className="bg-blue-50 border border-blue-200 rounded-md p-4">
                                        <h4 className="text-sm font-medium text-blue-900 mb-2">
                                            How to find your Last.fm username:
                                        </h4>
                                        <ol className="text-sm text-blue-800 space-y-1">
                                            <li>1. Go to Last.fm and log in to your account</li>
                                            <li>2. Click on your profile picture in the top right</li>
                                            <li>3. Your username is displayed in the URL and on your profile page</li>
                                        </ol>
                                    </div>

                                    <div className="flex items-center justify-end">
                                        <PrimaryButton className="ml-4" disabled={processing}>
                                            {processing ? 'Connecting...' : 'Connect Last.fm'}
                                        </PrimaryButton>
                                    </div>
                                </form>

                                <div className="mt-8 p-4 bg-gray-50 rounded-md">
                                    <h4 className="text-sm font-medium text-gray-900 mb-2">
                                        What happens next?
                                    </h4>
                                    <ul className="text-sm text-gray-600 space-y-1">
                                        <li>• We'll validate your Last.fm username</li>
                                        <li>• Import your current weekly top albums</li>
                                        <li>• Create your first chart automatically</li>
                                        <li>• You can then customize and share your charts</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}