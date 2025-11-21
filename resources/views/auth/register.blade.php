<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - InvoiceAI</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen flex">
        <!-- Left side - Branding -->
        <div class="hidden md:flex md:w-2/5 lg:w-2/5 bg-gradient-to-br from-indigo-600 via-purple-600 to-indigo-800 p-8 lg:p-12 flex-col justify-between relative overflow-hidden">
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
                <a href="/" class="inline-block">
                    <img src="/images/logo-white.svg" alt="InvoiceAI" class="h-8 lg:h-10">
                </a>
            </div>
            
            <!-- Hero Illustration - Dashboard mockup -->
            <div class="relative z-10 flex-1 flex items-center justify-center py-6">
                <svg class="w-full max-w-xs" viewBox="0 0 320 240" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <!-- Browser window -->
                    <rect x="20" y="20" width="280" height="200" rx="8" fill="white" fill-opacity="0.95"/>
                    
                    <!-- Browser header -->
                    <rect x="20" y="20" width="280" height="30" rx="8" fill="#F3F4F6"/>
                    <rect x="20" y="42" width="280" height="8" fill="#F3F4F6"/>
                    <circle cx="35" cy="35" r="4" fill="#FCA5A5"/>
                    <circle cx="50" cy="35" r="4" fill="#FCD34D"/>
                    <circle cx="65" cy="35" r="4" fill="#6EE7B7"/>
                    
                    <!-- Sidebar -->
                    <rect x="20" y="50" width="60" height="170" fill="#EEF2FF"/>
                    <rect x="30" y="65" width="40" height="6" rx="2" fill="#A5B4FC"/>
                    <rect x="30" y="85" width="35" height="4" rx="1" fill="#C7D2FE"/>
                    <rect x="30" y="100" width="38" height="4" rx="1" fill="#C7D2FE"/>
                    <rect x="30" y="115" width="32" height="4" rx="1" fill="#C7D2FE"/>
                    <rect x="30" y="130" width="36" height="4" rx="1" fill="#818CF8"/>
                    
                    <!-- Main content area -->
                    <!-- Stats cards -->
                    <rect x="90" y="60" width="60" height="40" rx="4" fill="#EEF2FF"/>
                    <rect x="160" y="60" width="60" height="40" rx="4" fill="#ECFDF5"/>
                    <rect x="230" y="60" width="60" height="40" rx="4" fill="#FEF3C7"/>
                    
                    <rect x="98" y="70" width="30" height="6" rx="2" fill="#6366F1"/>
                    <rect x="98" y="82" width="44" height="4" rx="1" fill="#A5B4FC"/>
                    
                    <rect x="168" y="70" width="30" height="6" rx="2" fill="#10B981"/>
                    <rect x="168" y="82" width="44" height="4" rx="1" fill="#6EE7B7"/>
                    
                    <rect x="238" y="70" width="30" height="6" rx="2" fill="#F59E0B"/>
                    <rect x="238" y="82" width="44" height="4" rx="1" fill="#FCD34D"/>
                    
                    <!-- Table -->
                    <rect x="90" y="115" width="200" height="95" rx="4" fill="white" stroke="#E5E7EB" stroke-width="1"/>
                    <rect x="90" y="115" width="200" height="20" rx="4" fill="#F9FAFB"/>
                    <rect x="100" y="122" width="40" height="4" rx="1" fill="#9CA3AF"/>
                    <rect x="160" y="122" width="30" height="4" rx="1" fill="#9CA3AF"/>
                    <rect x="220" y="122" width="50" height="4" rx="1" fill="#9CA3AF"/>
                    
                    <!-- Table rows -->
                    <rect x="100" y="145" width="50" height="4" rx="1" fill="#D1D5DB"/>
                    <rect x="160" y="145" width="35" height="4" rx="1" fill="#D1D5DB"/>
                    <rect x="220" y="143" width="40" height="8" rx="2" fill="#DCFCE7"/>
                    
                    <rect x="100" y="165" width="45" height="4" rx="1" fill="#D1D5DB"/>
                    <rect x="160" y="165" width="40" height="4" rx="1" fill="#D1D5DB"/>
                    <rect x="220" y="163" width="40" height="8" rx="2" fill="#FEF3C7"/>
                    
                    <rect x="100" y="185" width="55" height="4" rx="1" fill="#D1D5DB"/>
                    <rect x="160" y="185" width="30" height="4" rx="1" fill="#D1D5DB"/>
                    <rect x="220" y="183" width="40" height="8" rx="2" fill="#DCFCE7"/>
                    
                    <!-- Decorative elements -->
                    <circle cx="15" cy="100" r="3" fill="#FCD34D"/>
                    <circle cx="310" cy="180" r="2" fill="#A78BFA"/>
                    <circle cx="305" cy="40" r="3" fill="#F472B6"/>
                </svg>
            </div>
            
            <!-- Features list -->
            <div class="relative z-10 space-y-3">
                <h3 class="text-white font-semibold mb-4">Start in minutes</h3>
                <div class="flex items-center space-x-3 text-white/90">
                    <svg class="w-5 h-5 text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span class="text-sm">AI-powered extraction</span>
                </div>
                <div class="flex items-center space-x-3 text-white/90">
                    <svg class="w-5 h-5 text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span class="text-sm">ERP integration ready</span>
                </div>
                <div class="flex items-center space-x-3 text-white/90">
                    <svg class="w-5 h-5 text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span class="text-sm">14-day free trial</span>
                </div>
            </div>
            
            <p class="relative z-10 text-indigo-200 text-sm mt-6">&copy; {{ date('Y') }} InvoiceAI. All rights reserved.</p>
        </div>

        <!-- Right side - Register Form -->
        <div class="w-full md:w-3/5 lg:w-3/5 flex items-center justify-center p-4 sm:p-6 lg:p-12">
            <div class="w-full max-w-lg">
                <!-- Mobile logo -->
                <div class="md:hidden text-center mb-6">
                    <a href="/">
                        <img src="/images/logo.svg" alt="InvoiceAI" class="h-8 mx-auto">
                    </a>
                </div>

                <div class="bg-white rounded-2xl shadow-xl p-5 sm:p-6 lg:p-8">
                    <div class="text-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Create your account</h2>
                        <p class="text-gray-500 mt-2">Start your 14-day free trial</p>
                    </div>

                    <form method="POST" action="{{ route('register') }}" class="space-y-4">
                        @csrf

                        <!-- Company & Name Row -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Company Name -->
                            <div>
                                <label for="company_name" class="block text-sm font-medium text-gray-700 mb-1.5">Company name</label>
                                <input type="text" id="company_name" name="company_name" value="{{ old('company_name') }}" required autofocus
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors @error('company_name') border-red-500 @enderror"
                                    placeholder="Acme Inc.">
                                @error('company_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Full Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Your name</label>
                                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors @error('name') border-red-500 @enderror"
                                    placeholder="John Doe">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email address</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors @error('email') border-red-500 @enderror"
                                placeholder="you@company.com">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password Row -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Password -->
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                                <input type="password" id="password" name="password" required
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors @error('password') border-red-500 @enderror"
                                    placeholder="••••••••">
                                @error('password')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Confirm Password -->
                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">Confirm password</label>
                                <input type="password" id="password_confirmation" name="password_confirmation" required
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                    placeholder="••••••••">
                            </div>
                        </div>

                        <!-- Plan Selection -->
                        @php
                            $plans = \App\Models\Plan::active()->ordered()->get();
                            $selectedPlan = old('plan_id') ?? request('plan');
                        @endphp

                        @if($plans->isNotEmpty())
                            <div class="mt-2">
                                <label class="block text-sm font-medium text-gray-700 mb-3">Select your plan</label>
                                <div class="grid grid-cols-1 sm:grid-cols-{{ min($plans->count(), 3) }} gap-3">
                                    @foreach($plans as $plan)
                                        <label class="relative cursor-pointer group">
                                            <input type="radio" name="plan_id" value="{{ $plan->id }}" 
                                                {{ ($selectedPlan == $plan->slug || $selectedPlan == $plan->id || (!$selectedPlan && $plan->isProfessional())) ? 'checked' : '' }}
                                                class="peer sr-only">
                                            <div class="p-4 rounded-xl border-2 border-gray-200 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 hover:border-gray-300 transition-all">
                                                <div class="flex items-center justify-between mb-2">
                                                    <span class="font-semibold text-gray-900">{{ $plan->name }}</span>
                                                    <div class="w-4 h-4 rounded-full border-2 border-gray-300 peer-checked:border-indigo-500 peer-checked:bg-indigo-500 flex items-center justify-center">
                                                        <svg class="w-2.5 h-2.5 text-white hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <div class="text-2xl font-bold text-gray-900">
                                                    {{ $plan->formatted_price }}
                                                    <span class="text-sm font-normal text-gray-500">/mo</span>
                                                </div>
                                                <div class="text-sm text-gray-500 mt-1">
                                                    {{ number_format($plan->invoice_limit) }} invoices/month
                                                </div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                                @error('plan_id')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

                        <!-- Terms -->
                        <div class="flex items-start">
                            <input type="checkbox" id="terms" name="terms" required
                                class="w-4 h-4 mt-0.5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            <label for="terms" class="ml-2 text-sm text-gray-600">
                                I agree to the <a href="#" class="text-indigo-600 hover:text-indigo-500">Terms of Service</a> 
                                and <a href="#" class="text-indigo-600 hover:text-indigo-500">Privacy Policy</a>
                            </label>
                        </div>

                        <!-- Submit -->
                        <button type="submit" 
                            class="w-full py-3 px-4 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                            Start free trial
                        </button>
                    </form>

                    <!-- Login link -->
                    <p class="mt-6 text-center text-sm text-gray-600">
                        Already have an account?
                        <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-500 font-semibold">
                            Sign in
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
