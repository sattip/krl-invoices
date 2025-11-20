@extends('layouts.guest')

@section('content')
<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
    <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
        <h2 class="text-2xl font-bold text-center mb-6">Complete Your Registration</h2>

        @if(session('error'))
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                {{ session('error') }}
            </div>
        @endif

        @if(session('info'))
            <div class="mb-4 p-4 bg-blue-100 border border-blue-400 text-blue-700 rounded">
                {{ session('info') }}
            </div>
        @endif

        <div class="mb-6">
            <h3 class="text-lg font-semibold mb-4">Order Summary</h3>

            <div class="bg-gray-50 p-4 rounded-lg">
                <div class="flex justify-between items-center mb-2">
                    <span class="font-medium">{{ $plan->name }} Plan</span>
                    <span class="font-bold">{{ $plan->formatted_price }}/mo</span>
                </div>

                <div class="text-sm text-gray-600 mb-2">
                    <span class="inline-block w-4">✓</span> {{ number_format($plan->invoice_limit) }} invoices/month
                </div>
                <div class="text-sm text-gray-600 mb-2">
                    <span class="inline-block w-4">✓</span> AI-powered extraction
                </div>
                <div class="text-sm text-gray-600 mb-2">
                    <span class="inline-block w-4">✓</span> API access
                </div>

                <hr class="my-4">

                <div class="text-sm text-gray-600">
                    <strong>Account:</strong> {{ $registration['email'] }}
                </div>
                <div class="text-sm text-gray-600">
                    <strong>Company:</strong> {{ $registration['company_name'] }}
                </div>
            </div>
        </div>

        <form action="{{ route('register.process-checkout') }}" method="POST">
            @csrf
            <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                </svg>
                Proceed to Payment
            </button>
        </form>

        <p class="text-xs text-gray-500 text-center mt-4">
            You'll be redirected to Stripe's secure checkout page to complete your payment.
        </p>

        <div class="mt-6 text-center">
            <a href="{{ route('register') }}" class="text-sm text-indigo-600 hover:text-indigo-500">
                ← Back to registration
            </a>
        </div>
    </div>
</div>
@endsection
