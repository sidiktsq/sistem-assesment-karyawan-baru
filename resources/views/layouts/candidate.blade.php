<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Assessment</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    @php
        $buildExists = file_exists(public_path('build/manifest.json'));
        $hotExists = file_exists(public_path('hot'));
    @endphp

    @if($buildExists || $hotExists)
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <!-- Fallback to CDN if Vite assets are not built -->
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        fontFamily: {
                            sans: ['Outfit', 'sans-serif'],
                        },
                        colors: {
                            slate: {
                                950: '#020617',
                            }
                        }
                    }
                }
            }
        </script>
    @endif
    @livewireStyles

    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }
        [x-cloak] { display: none !important; }
        .glass-panel {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .premium-gradient {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        }
        .timer-critical {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: .5; }
        }
    </style>
</head>
<body class="h-full text-slate-200 antialiased selection:bg-blue-500/30">
    <div class="min-h-full flex flex-col">
        <!-- Header -->
        <header class="glass-panel sticky top-0 z-40 border-b border-white/5">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg premium-gradient flex items-center justify-center shadow-lg shadow-blue-500/20">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="text-lg font-bold tracking-tight text-white uppercase">Sistem Asesmen</span>
                    </div>

                    @yield('header_extra')
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-grow">
            <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
                {{ $slot ?? '' }}
                @yield('content')
            </div>
        </main>

        <!-- Footer -->
        <footer class="border-t border-white/5 py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-slate-500 text-sm">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </div>
        </footer>
    </div>

    @livewireScripts
    
    <script>
        // Global exam functions
        window.examConfig = {
            apiBaseUrl: '{{ url('/api') }}',
            token: '{{ $token ?? '' }}',
            autoSaveInterval: 30000, // 30 seconds
        };
        
        // Prevent accidental navigation
        window.addEventListener('beforeunload', function (e) {
            if (document.querySelector('.exam-active')) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
        
        // Prevent back button during exam
        history.pushState(null, null, location.href);
        window.onpopstate = function () {
            if (document.querySelector('.exam-active')) {
                history.go(1);
            }
        };
    </script>
    
    @stack('scripts')
</body>
</html>
