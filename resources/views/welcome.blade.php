<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Discord Security Bot Status</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
        
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <script src="https://cdn.tailwindcss.com"></script>
            <script>
                tailwind.config = {
                    darkMode: 'media',
                    theme: {
                        extend: {
                            fontFamily: {
                                sans: ['Instrument Sans', 'sans-serif'],
                            },
                        }
                    }
                }
            </script>
        @endif
    </head>
    <body class="bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 min-h-screen flex items-center justify-center font-sans antialiased selection:bg-red-500 selection:text-white">
        <div class="max-w-4xl w-full mx-auto p-4 sm:p-6 lg:p-8">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden ring-1 ring-gray-900/5 dark:ring-white/10">
                <!-- Header -->
                <div class="px-6 py-8 sm:px-10 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-b from-white to-gray-50 dark:from-gray-800 dark:to-gray-800/50">
                     <div class="flex flex-col items-center">
                        <div class="h-16 w-16 bg-red-100 dark:bg-red-900/30 rounded-2xl flex items-center justify-center mb-4 shadow-sm text-3xl">
                            🛡️
                        </div>
                        <h1 class="text-3xl font-bold text-center text-gray-900 dark:text-white tracking-tight">Security Monitor Bot</h1>
                        <p class="text-center text-gray-500 dark:text-gray-400 mt-2 max-w-md">Real-time security advisory monitoring for the Laravel ecosystem</p>
                     </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-gray-100 dark:divide-gray-700">
                    <!-- Bot Status Section -->
                    <div class="p-8 sm:p-10 flex flex-col items-center justify-center bg-white dark:bg-gray-800 transition-colors">
                        <h2 class="text-xs uppercase tracking-wider font-semibold text-gray-400 dark:text-gray-500 mb-6">System Status</h2>
                        
                        <div class="flex flex-col items-center space-y-4">
                            @if($status === 'Operational')
                                <div class="relative flex items-center justify-center h-20 w-20">
                                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-20"></span>
                                  <span class="relative inline-flex rounded-full h-4 w-4 bg-green-500 shadow-[0_0_20px_rgba(34,197,94,0.5)]"></span>
                                </div>
                                <div class="text-center -mt-4">
                                    <span class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight block">{{ $status }}</span>
                                    <span class="text-sm text-green-600 dark:text-green-400 font-medium bg-green-50 dark:bg-green-900/20 px-3 py-1 rounded-full mt-2 inline-block">All systems normal</span>
                                </div>
                            @elseif($status === 'Delayed')
                                <div class="h-20 w-20 flex items-center justify-center">
                                    <span class="relative inline-flex rounded-full h-4 w-4 bg-yellow-500 shadow-[0_0_20px_rgba(234,179,8,0.5)]"></span>
                                </div>
                                <div class="text-center -mt-4">
                                    <span class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight block">{{ $status }}</span>
                                    <span class="text-sm text-yellow-600 dark:text-yellow-400 font-medium bg-yellow-50 dark:bg-yellow-900/20 px-3 py-1 rounded-full mt-2 inline-block">Scan delayed</span>
                                </div>
                            @else
                                <div class="h-20 w-20 flex items-center justify-center">
                                    <span class="relative inline-flex rounded-full h-4 w-4 bg-gray-400 shadow-[0_0_20px_rgba(156,163,175,0.5)]"></span>
                                </div>
                                <div class="text-center -mt-4">
                                    <span class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight block">{{ $status }}</span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400 font-medium bg-gray-100 dark:bg-gray-800 px-3 py-1 rounded-full mt-2 inline-block">Waiting for first scan</span>
                                </div>
                            @endif
                        </div>

                        <div class="mt-8 pt-6 border-t border-dashed border-gray-200 dark:border-gray-700 w-full text-center">
                            <p class="text-sm text-gray-500 dark:text-gray-400 flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Last scan: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $lastScan ? $lastScan->diffForHumans() : 'Never' }}</span>
                            </p>
                        </div>
                    </div>

                    <!-- Latest Update Section -->
                    <div class="p-8 sm:p-10 bg-gray-50/50 dark:bg-gray-800/50 flex flex-col">
                        <h2 class="text-xs uppercase tracking-wider font-semibold text-gray-400 dark:text-gray-500 mb-6">Latest Security Advisory</h2>
                        
                        @if($lastUpdate)
                            <div class="flex-1 flex flex-col relative group">
                                <div class="absolute -inset-4 bg-gradient-to-r from-red-50 to-orange-50 dark:from-red-900/5 dark:to-orange-900/5 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative">
                                    <div class="flex justify-between items-start mb-4">
                                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-bold tracking-wide uppercase border {{ $lastUpdate->severity === 'CRITICAL' ? 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/20 dark:text-red-400 dark:border-red-900/30' : 'bg-orange-50 text-orange-700 border-orange-200 dark:bg-orange-900/20 dark:text-orange-400 dark:border-orange-900/30' }}">
                                            {{ $lastUpdate->severity }}
                                        </span>
                                        <time class="text-xs text-gray-400 font-medium tabular-nums" datetime="{{ $lastUpdate->published_at }}">
                                            {{ $lastUpdate->published_at?->format('M d, Y') }}
                                        </time>
                                    </div>
                                    
                                    <h3 class="text-lg leading-snug font-bold text-gray-900 dark:text-white mb-2 group-hover:text-red-600 dark:group-hover:text-red-400 transition-colors">
                                        {{ $lastUpdate->title }}
                                    </h3>
                                    
                                    <div class="flex items-center gap-2 mb-3 text-sm text-gray-500 dark:text-gray-400 font-medium">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                        {{ $lastUpdate->framework_name }}
                                        @if($lastUpdate->cve_id)
                                            <span class="text-gray-300 dark:text-gray-600">•</span>
                                            <span class="font-mono text-xs bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded text-gray-600 dark:text-gray-300">{{ $lastUpdate->cve_id }}</span>
                                        @endif
                                    </div>

                                    <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed line-clamp-4 mb-6">
                                        {{ $lastUpdate->description }}
                                    </p>

                                    <div class="mt-auto">
                                        <a href="{{ $lastUpdate->reference_url }}" target="_blank" class="inline-flex items-center text-sm font-semibold text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 transition-colors">
                                            Read Full Advisory
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1.5 group-hover/link:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="flex-1 flex flex-col items-center justify-center text-center py-12">
                                <div class="h-12 w-12 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4 text-2xl grayscale opacity-50">
                                    ✅
                                </div>
                                <p class="text-gray-500 dark:text-gray-400 font-medium">No active security advisories</p>
                                <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Check back later for updates</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center text-xs text-gray-400 dark:text-gray-500">
                    <div>
                        Current Time: <span class="font-mono">{{ now()->format('Y-m-d H:i:s') }}</span>
                    </div>
                    <div>
                        App v1.0.0
                    </div>
                </div>
            </div>
        </div>
        
        <script>
            // Simple auto-refresh every 60 seconds to keep status up to date
            setTimeout(function() {
                window.location.reload();
            }, 60000);
        </script>
    </body>
</html>
