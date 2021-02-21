@extends('page-cache::layout')

@section('content')
    <div class="px-4 sm:px-6 md:px-0">
        <h1 class="text-3xl font-extrabold text-gray-900">Install manual</h1>
    </div>

    <div x-data="{ tab: 'apache' }" class="px-4 sm:px-6 md:px-0 py-6">
        <nav class="flex border-b border-gray-200">
            <button
                :class="{ 'border-blue-600 text-blue-600 hover:border-blue-600 hover:text-blue-600': tab === 'apache' }"
                @click="tab = 'apache'"
                class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 focus:outline-none whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                Apache
            </button>
            <button
                :class="{ 'border-blue-600 text-blue-600 hover:border-blue-600 hover:text-blue-600': tab === 'nginx' }"
                @click="tab = 'nginx'"
                class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 focus:outline-none whitespace-nowrap ml-8 py-4 px-1 border-b-2 font-medium text-sm">
                Nginx
            </button>
        </nav>

        <div x-show="tab === 'apache'">
            @include('page-cache::partials.install-apache')
        </div>

        <div x-show="tab === 'nginx'">
            @include('page-cache::partials.install-nginx')
        </div>
    </div>
@endsection
