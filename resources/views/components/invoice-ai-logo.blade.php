@props(['class' => 'h-9 w-auto', 'showText' => true])

<div {{ $attributes->merge(['class' => $class . ' flex items-center']) }}>
    <svg class="h-full w-auto" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
        <!-- Document shape -->
        <rect x="4" y="2" width="24" height="32" rx="3" fill="url(#logoGrad1)" />
        <!-- Folded corner -->
        <path d="M20 2L28 10H20V2Z" fill="#A5B4FC" />
        <!-- Lines representing text -->
        <rect x="8" y="12" width="16" height="2" rx="1" fill="white" opacity="0.9" />
        <rect x="8" y="17" width="12" height="2" rx="1" fill="white" opacity="0.7" />
        <rect x="8" y="22" width="14" height="2" rx="1" fill="white" opacity="0.5" />
        <rect x="8" y="27" width="8" height="2" rx="1" fill="white" opacity="0.3" />
        <!-- AI Badge -->
        <circle cx="30" cy="30" r="8" fill="url(#logoGrad2)" />
        <text x="30" y="33" font-family="Arial, sans-serif" font-size="7" font-weight="bold" fill="white" text-anchor="middle">AI</text>
        <defs>
            <linearGradient id="logoGrad1" x1="4" y1="2" x2="28" y2="34" gradientUnits="userSpaceOnUse">
                <stop stop-color="#4F46E5" />
                <stop offset="1" stop-color="#7C3AED" />
            </linearGradient>
            <linearGradient id="logoGrad2" x1="22" y1="22" x2="38" y2="38" gradientUnits="userSpaceOnUse">
                <stop stop-color="#F59E0B" />
                <stop offset="1" stop-color="#EF4444" />
            </linearGradient>
        </defs>
    </svg>
    @if($showText)
    <span class="ml-2 text-xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
        InvoiceAI
    </span>
    @endif
</div>
