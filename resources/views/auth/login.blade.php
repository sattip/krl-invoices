<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - InvoiceAI</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen flex">
        <!-- Left side - Branding -->
        <div class="hidden md:flex md:w-1/2 lg:w-1/2 bg-gradient-to-br from-indigo-600 via-purple-600 to-indigo-800 p-8 lg:p-12 flex-col justify-between relative overflow-hidden">
            <!-- Background pattern -->
            <div class="absolute inset-0 opacity-10">
                <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                    <defs>
                        <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
                            <circle cx="1" cy="1" r="1" fill="white"/>
                        </pattern>
                    </defs>
                    <rect width="100" height="100" fill="url(#grid)"/>
                </svg>
            </div>

            <!-- Logo -->
            <div class="relative z-10">
                <a href="/" class="flex items-center space-x-3">
                    <img src="/images/logo-white.svg" alt="InvoiceAI" class="h-8 lg:h-10">
                </a>
            </div>
            
            <!-- Hero Illustration -->
            <div class="relative z-10 flex-1 flex items-center justify-center py-8">
                <svg class="w-full max-w-xs lg:max-w-sm" viewBox="0 0 400 300" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <!-- Document base -->
                    <rect x="100" y="40" width="200" height="240" rx="8" fill="white" fill-opacity="0.95"/>
                    <rect x="100" y="40" width="200" height="50" rx="8" fill="white"/>
                    <rect x="100" y="82" width="200" height="198" fill="white" fill-opacity="0.95"/>
                    
                    <!-- Document header -->
                    <rect x="120" y="60" width="80" height="8" rx="2" fill="#E0E7FF"/>
                    <rect x="260" y="60" width="20" height="8" rx="2" fill="#C7D2FE"/>
                    
                    <!-- Document lines -->
                    <rect x="120" y="110" width="160" height="6" rx="2" fill="#E0E7FF"/>
                    <rect x="120" y="130" width="140" height="6" rx="2" fill="#EEF2FF"/>
                    <rect x="120" y="150" width="160" height="6" rx="2" fill="#E0E7FF"/>
                    <rect x="120" y="170" width="100" height="6" rx="2" fill="#EEF2FF"/>
                    
                    <!-- Table section -->
                    <rect x="120" y="200" width="160" height="1" fill="#C7D2FE"/>
                    <rect x="120" y="210" width="60" height="6" rx="2" fill="#A5B4FC"/>
                    <rect x="220" y="210" width="40" height="6" rx="2" fill="#818CF8"/>
                    <rect x="120" y="230" width="60" height="6" rx="2" fill="#A5B4FC"/>
                    <rect x="220" y="230" width="40" height="6" rx="2" fill="#818CF8"/>
                    <rect x="120" y="250" width="160" height="1" fill="#C7D2FE"/>
                    
                    <!-- AI Processing indicator -->
                    <circle cx="320" cy="120" r="35" fill="white" fill-opacity="0.2"/>
                    <circle cx="320" cy="120" r="25" fill="white" fill-opacity="0.3"/>
                    <path d="M310 115L318 123L330 111" stroke="#34D399" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                    
                    <!-- Sparkles -->
                    <circle cx="85" cy="80" r="3" fill="#FCD34D"/>
                    <circle cx="340" cy="200" r="2" fill="#FCD34D"/>
                    <circle cx="70" cy="180" r="2" fill="#A78BFA"/>
                    <circle cx="350" cy="70" r="3" fill="#F472B6"/>
                    
                    <!-- Floating elements -->
                    <rect x="50" y="120" width="30" height="30" rx="6" fill="white" fill-opacity="0.2" transform="rotate(-12 65 135)"/>
                    <rect x="330" y="220" width="25" height="25" rx="5" fill="white" fill-opacity="0.15" transform="rotate(8 342 232)"/>
                </svg>
            </div>

            <!-- Features -->
            <div class="relative z-10 space-y-3">
                <div class="flex items-center space-x-3 text-white/90">
                    <svg class="w-5 h-5 text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span class="text-sm">AI-powered data extraction</span>
                </div>
                <div class="flex items-center space-x-3 text-white/90">
                    <svg class="w-5 h-5 text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span class="text-sm">Enterprise-grade security</span>
                </div>
            </div>
            
            <p class="relative z-10 text-indigo-200 text-sm mt-6">&copy; {{ date('Y') }} InvoiceAI. All rights reserved.</p>
        </div>

        <!-- Right side - Login Form -->
        <div class="w-full md:w-1/2 lg:w-1/2 flex items-center justify-center p-6 sm:p-8 lg:p-12 bg-gray-50">
            <div class="w-full max-w-md">
                <!-- Mobile logo -->
                <div class="md:hidden text-center mb-8">
                    <a href="/">
                        <img src="/images/logo.svg" alt="InvoiceAI" class="h-8 mx-auto">
                    </a>
                </div>

                <div class="bg-white rounded-2xl shadow-xl p-6 sm:p-8">
                    <div class="text-center mb-8">
                        <h2 class="text-2xl font-bold text-gray-900">Welcome back</h2>
                        <p class="text-gray-500 mt-2">Sign in to your account</p>
                    </div>

                    @if (session('status'))
                        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email address</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                                class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors @error('email') border-red-500 @enderror"
                                placeholder="you@example.com">
                            @error('email')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                            <input type="password" id="password" name="password" required
                                class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors @error('password') border-red-500 @enderror"
                                placeholder="••••••••">
                            @error('password')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Remember & Forgot -->
                        <div class="flex items-center justify-between">
                            <label class="flex items-center">
                                <input type="checkbox" name="remember" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-600">Remember me</span>
                            </label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-sm text-indigo-600 hover:text-indigo-500 font-medium">
                                    Forgot password?
                                </a>
                            @endif
                        </div>

                        <!-- Submit -->
                        <button type="submit" 
                            class="w-full py-3 px-4 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                            Sign in
                        </button>
                    </form>

                    <!-- Register link -->
                    <p class="mt-6 text-center text-sm text-gray-600">
                        Don't have an account?
                        <a href="{{ route('register') }}" class="text-indigo-600 hover:text-indigo-500 font-semibold">
                            Create one now
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
