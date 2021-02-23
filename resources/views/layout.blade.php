<!DOCTYPE html>
<html lang="en">
<head>
    <title>Page cache</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta content="telephone=no" name="format-detection" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="{{ asset('/vendor/assets/css/highlight.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/vendor/assets/css/tailwind.min.css') }}"/>
    <script src="{{ asset('/vendor/assets/js/highlight.min.js') }}"></script>
    <script src="{{ asset('/vendor/assets/js/apache.min.js') }}"></script>
    <script src="{{ asset('/vendor/assets/js/alpine.min.js') }}" defer></script>
    <script>hljs.highlightAll();</script>
</head>

<body class="h-screen">

<div class="h-screen bg-white overflow-hidden flex">
        <div class="w-64 flex flex-col flex-shrink-0">
            <nav class="bg-gray-50 border-r border-gray-200 pt-5 pb-4 pr-5 flex flex-col flex-grow overflow-y-auto">
                <div class="flex-shrink-0 px-4 flex items-center">
                    <img class="h-8 w-auto" src="{{ asset('/vendor/assets/images/logo.svg') }}" alt="Page Cache Laravel">
                </div>
                <div class="flex-grow mt-12 flex flex-col">
                    <a href="#" class="bg-blue-50 border-blue-600 text-gray-600 group border-l-4 rounded-r-full flex items-center py-3 px-3 text-sm font-medium" aria-current="page">
                        <svg class="text-blue-600 mr-3 h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Install
                    </a>
                </div>
                <div class="flex-shrink-0 block w-full">
                    <a href="https://github.com/siteorigin/laravel-page-cache" target="_blank" class="group rounded-md py-2 px-4 flex items-center text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-50">
                        <svg class="text-purple-500 mr-3 h-6 w-6" height="32" viewBox="0 0 16 16" version="1.1" width="32" aria-hidden="true"><path fill-rule="evenodd" d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0016 8c0-4.42-3.58-8-8-8z"></path></svg>
                        GitHub
                    </a>
                </div>
            </nav>
        </div>

    <div class="flex-1 flex flex-col">
        <main class="flex-1 overflow-y-auto focus:outline-none">
            <div class="relative max-w-4xl mx-auto md:px-8 xl:px-0 pt-10 pb-16">
                @yield('content')
            </div>
        </main>
    </div>
</div>
</body>
</html>
