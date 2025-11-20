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
        <div class="hidden lg:flex lg:w-2/5 bg-gradient-to-br from-indigo-600 via-purple-600 to-indigo-800 p-12 flex-col justify-between">
            <div>
                <a href="/" class="inline-block">
                    <h1 class="text-white text-3xl font-bold">InvoiceAI</h1>
                </a>
                <p class="text-indigo-200 mt-2">Smart Invoice Processing</p>
            </div>
            
            <div class="space-y-4">
                <h2 class="text-white text-xl font-semibold">Start processing invoices in minutes</h2>
                <ul class="space-y-3">
                    <li class="flex items-center text-indigo-100">
                        <svg class="w-5 h-5 mr-3 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        AI-powered data extraction
                    </li>
                    <li class="flex items-center text-indigo-100">
                        <svg class="w-5 h-5 mr-3 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        ERP integration ready
                    </li>
                    <li class="flex items-center text-indigo-100">
                        <svg class="w-5 h-5 mr-3 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        99.9% accuracy rate
                    </li>
                    <li class="flex items-center text-indigo-100">
                        <svg class="w-5 h-5 mr-3 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        14-day free trial
                    </li>
                </ul>
            </div>
            
            <p class="text-indigo-200 text-sm">&copy; {{ date('Y') }} InvoiceAI. All rights reserved.</p>
        </div>

        <!-- Right side - Register Form -->
        <div class="w-full lg:w-3/5 flex items-center justify-center p-6 lg:p-12">
            <div class="w-full max-w-xl">
                <!-- Mobile logo -->
                <div class="lg:hidden text-center mb-6">
                    <h1 class="text-2xl font-bold text-indigo-600">InvoiceAI</h1>
                </div>

                <div class="bg-white rounded-2xl shadow-xl p-6 lg:p-8">
                    <div class="text-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Create your account</h2>
                        <p class="text-gray-500 mt-2">Start your 14-day free trial</p>
                    </div>

                    <form method="POST" action="{{ route('register') }}" class="space-y-4">
                        @csrf

                        <!-- Company & Name Row -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                                <div class="grid grid-cols-1 md:grid-cols-{{ min($plans->count(), 3) }} gap-3">
                                    @foreach($plans as $plan)
                                        <label class="relative cursor-pointer">
                                            <input type="radio" name="plan_id" value="{{ $plan->id }}" 
                                                {{ ($selectedPlan == $plan->slug || $selectedPlan == $plan->id || (!$selectedPlan && $plan->isProfessional())) ? 'checked' : '' }}
                                                class="peer sr-only">
                                            <div class="p-4 rounded-xl border-2 border-gray-200 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 hover:border-gray-300 transition-all">
                                                <div class="flex items-center justify-between mb-2">
                                                    <span class="font-semibold text-gray-900">{{ $plan->name }}</span>
                                                    <div class="w-4 h-4 rounded-full border-2 border-gray-300 peer-checked:border-indigo-500 peer-checked:bg-indigo-500 flex items-center justify-center">
                                                        <div class="w-1.5 h-1.5 rounded-full bg-white hidden peer-checked:block"></div>
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
