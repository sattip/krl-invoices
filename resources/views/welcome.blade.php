<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="InvoiceAI - AI-Powered Invoice Data Extraction. Upload invoices and automatically extract structured data with high accuracy.">

    <title>InvoiceAI - AI-Powered Invoice Parser</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
</head>
<body>
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <!-- Navigation -->
            <nav class="landing-nav">
                <div class="container">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="/" class="nav-logo">
                            <img src="{{ asset('images/logo-white.svg') }}" alt="InvoiceAI" height="40">
                        </a>
                        <div>
                            @auth
                                <a href="{{ route('dashboard') }}" class="btn btn-outline-white">Dashboard</a>
                            @else
                                @if (Route::has('login'))
                                    <a href="{{ route('login') }}" class="btn btn-outline-white mr-2">Log in</a>
                                @endif
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="btn btn-light">Get Started Free</a>
                                @endif
                            @endauth
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Hero Content -->
            <div class="container py-5">
                <div class="row align-items-center">
                    <div class="col-lg-6 text-white">
                        <h1 class="display-4 font-weight-bold mb-4">
                            Extract Invoice Data with <span style="color: #F59E0B;">AI Precision</span>
                        </h1>
                        <p class="lead mb-4" style="opacity: 0.9;">
                            Upload invoices and let our AI automatically extract line items, totals, parties, and more.
                            Connect directly to your ERP system for seamless data flow.
                        </p>
                        <div class="mb-4">
                            <a href="{{ route('register') }}" class="btn btn-accent btn-lg mr-3">
                                Start Free Trial
                            </a>
                            <a href="#how-it-works" class="btn btn-outline-white btn-lg">
                                <i class="fas fa-play-circle mr-2"></i> See How It Works
                            </a>
                        </div>
                        <div class="d-flex align-items-center flex-wrap" style="opacity: 0.8;">
                            <span class="mr-3 mb-2"><i class="fas fa-check-circle mr-1"></i> No credit card required</span>
                            <span class="mr-3 mb-2"><i class="fas fa-check-circle mr-1"></i> 99.5% accuracy</span>
                            <span class="mb-2"><i class="fas fa-check-circle mr-1"></i> 5 min setup</span>
                        </div>
                    </div>
                    <div class="col-lg-6 mt-5 mt-lg-0">
                        <!-- Hero Animation/Illustration -->
                        <div class="text-center">
                            <svg viewBox="0 0 400 300" fill="none" xmlns="http://www.w3.org/2000/svg" style="max-width: 100%;">
                                <!-- Invoice Document -->
                                <rect x="50" y="30" width="180" height="240" rx="8" fill="white" opacity="0.95"/>
                                <rect x="70" y="60" width="100" height="8" rx="4" fill="#E5E7EB"/>
                                <rect x="70" y="80" width="140" height="6" rx="3" fill="#E5E7EB"/>
                                <rect x="70" y="95" width="120" height="6" rx="3" fill="#E5E7EB"/>
                                <rect x="70" y="120" width="140" height="30" rx="4" fill="#F3F4F6"/>
                                <rect x="70" y="160" width="140" height="30" rx="4" fill="#F3F4F6"/>
                                <rect x="70" y="200" width="80" height="8" rx="4" fill="#4F46E5"/>
                                <rect x="160" y="200" width="50" height="8" rx="4" fill="#10B981"/>

                                <!-- Arrow -->
                                <path d="M250 150 L290 150" stroke="#F59E0B" stroke-width="4" stroke-linecap="round"/>
                                <path d="M280 140 L290 150 L280 160" stroke="#F59E0B" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>

                                <!-- JSON Output -->
                                <rect x="310" y="80" width="80" height="140" rx="8" fill="white" opacity="0.95"/>
                                <rect x="320" y="95" width="20" height="4" rx="2" fill="#4F46E5"/>
                                <rect x="345" y="95" width="35" height="4" rx="2" fill="#10B981"/>
                                <rect x="320" y="108" width="15" height="4" rx="2" fill="#4F46E5"/>
                                <rect x="340" y="108" width="40" height="4" rx="2" fill="#6B7280"/>
                                <rect x="320" y="121" width="20" height="4" rx="2" fill="#4F46E5"/>
                                <rect x="345" y="121" width="25" height="4" rx="2" fill="#F59E0B"/>
                                <rect x="320" y="134" width="18" height="4" rx="2" fill="#4F46E5"/>
                                <rect x="343" y="134" width="37" height="4" rx="2" fill="#6B7280"/>
                                <rect x="320" y="147" width="25" height="4" rx="2" fill="#4F46E5"/>
                                <rect x="350" y="147" width="30" height="4" rx="2" fill="#10B981"/>

                                <!-- AI Badge -->
                                <circle cx="230" cy="150" r="25" fill="#F59E0B"/>
                                <text x="230" y="156" font-family="Arial" font-size="12" font-weight="bold" fill="white" text-anchor="middle">AI</text>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Supported Formats -->
            <div class="container pb-5">
                <div class="text-center text-white" style="opacity: 0.7;">
                    <small class="text-uppercase font-weight-bold">Supported Formats</small>
                    <div class="mt-2">
                        <span class="badge badge-light mr-2"><i class="fas fa-file-pdf mr-1"></i> PDF</span>
                        <span class="badge badge-light mr-2"><i class="fas fa-file-image mr-1"></i> JPG</span>
                        <span class="badge badge-light"><i class="fas fa-file-image mr-1"></i> PNG</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Social Proof Bar -->
    <section class="social-proof-bar">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div class="stat-item">
                        <div class="stat-number">10,000+</div>
                        <div class="stat-label">Invoices Processed</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-item">
                        <div class="stat-number">99.5%</div>
                        <div class="stat-label">Extraction Accuracy</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-item">
                        <div class="stat-number">500+</div>
                        <div class="stat-label">Happy Businesses</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section id="how-it-works" class="section-padding">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">How It Works</h2>
                <p class="section-subtitle">Get started in minutes with our simple 3-step process</p>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="step-card fade-in">
                        <div class="step-number">1</div>
                        <i class="fas fa-cloud-upload-alt step-icon"></i>
                        <h4>Upload Invoice</h4>
                        <p class="text-muted">Upload your invoice in PDF, JPG, or PNG format through our web interface or API.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="step-card fade-in">
                        <div class="step-number">2</div>
                        <i class="fas fa-robot step-icon"></i>
                        <h4>AI Extracts Data</h4>
                        <p class="text-muted">Our AI analyzes the invoice and extracts all relevant data with high accuracy.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="step-card fade-in">
                        <div class="step-number">3</div>
                        <i class="fas fa-sync-alt step-icon"></i>
                        <h4>Sync to ERP</h4>
                        <p class="text-muted">Automatically send structured data to your ERP system or export as JSON/CSV.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ERP Integrations -->
    <section class="erp-section section-padding">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Connects to Your ERP</h2>
                <p class="section-subtitle">Seamless integration with popular Greek ERP systems via our REST API</p>
            </div>
            <div class="row justify-content-center">
                <div class="col-6 col-md-4 col-lg-2 mb-4">
                    <div class="erp-card">
                        <div class="erp-logo d-flex align-items-center justify-content-center">
                            <i class="fas fa-database fa-2x text-primary"></i>
                        </div>
                        <div class="erp-name">Soft1</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2 mb-4">
                    <div class="erp-card">
                        <div class="erp-logo d-flex align-items-center justify-content-center">
                            <i class="fas fa-server fa-2x text-primary"></i>
                        </div>
                        <div class="erp-name">Pylon</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2 mb-4">
                    <div class="erp-card">
                        <div class="erp-logo d-flex align-items-center justify-content-center">
                            <i class="fas fa-globe fa-2x text-primary"></i>
                        </div>
                        <div class="erp-name">Galaxy</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2 mb-4">
                    <div class="erp-card">
                        <div class="erp-logo d-flex align-items-center justify-content-center">
                            <i class="fas fa-cloud fa-2x text-primary"></i>
                        </div>
                        <div class="erp-name">ProsvasisGo</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2 mb-4">
                    <div class="erp-card">
                        <div class="erp-logo d-flex align-items-center justify-content-center">
                            <i class="fas fa-bolt fa-2x text-primary"></i>
                        </div>
                        <div class="erp-name">EpsilonSmart</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2 mb-4">
                    <div class="erp-card">
                        <div class="erp-logo d-flex align-items-center justify-content-center">
                            <i class="fas fa-code fa-2x text-primary"></i>
                        </div>
                        <div class="erp-name">REST API</div>
                    </div>
                </div>
            </div>
            <div class="text-center mt-4">
                <p class="text-muted mb-3">Don't see your ERP? Our REST API integrates with any system.</p>
                <a href="{{ route('register') }}" class="btn btn-primary-gradient">
                    <i class="fas fa-plug mr-2"></i> Connect Your ERP
                </a>
            </div>
        </div>
    </section>

    <!-- Features Grid -->
    <section class="section-padding">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Powerful Features</h2>
                <p class="section-subtitle">Everything you need to automate invoice processing</p>
            </div>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-brain"></i>
                        </div>
                        <h5>AI-Powered Extraction</h5>
                        <p class="text-muted">Advanced machine learning extracts data from any invoice format with 99.5% accuracy.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-table"></i>
                        </div>
                        <h5>Structured Output</h5>
                        <p class="text-muted">Get organized JSON with line items, totals, taxes, and party information.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-code"></i>
                        </div>
                        <h5>REST API</h5>
                        <p class="text-muted">Full-featured API for seamless integration with your existing workflows.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-layer-group"></i>
                        </div>
                        <h5>Batch Processing</h5>
                        <p class="text-muted">Process multiple invoices at once for maximum efficiency.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h5>Data Validation</h5>
                        <p class="text-muted">Automatic validation ensures accuracy before syncing to your ERP.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-file-export"></i>
                        </div>
                        <h5>Multiple Exports</h5>
                        <p class="text-muted">Export to JSON, CSV, or XML for compatibility with any system.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Black Friday Banner -->
    @php
        $blackFridayStart = \Carbon\Carbon::create(2025, 11, 20);
        $blackFridayEnd = \Carbon\Carbon::create(2025, 11, 30, 23, 59, 59);
        $showBlackFriday = now()->between($blackFridayStart, $blackFridayEnd);
    @endphp

    @if($showBlackFriday)
    <section class="black-friday-banner">
        <div class="container text-center position-relative" style="z-index: 1;">
            <span class="bf-badge">BLACK FRIDAY SPECIAL</span>
            <h2 class="text-white mb-3">
                <span class="bf-discount">50% OFF</span>
            </h2>
            <p class="text-white mb-4" style="opacity: 0.9;">All annual plans - Limited time offer!</p>

            <!-- Countdown -->
            <div class="mb-4" id="countdown">
                <div class="countdown-item">
                    <span class="countdown-number" id="days">00</span>
                    <span class="countdown-label">Days</span>
                </div>
                <div class="countdown-item">
                    <span class="countdown-number" id="hours">00</span>
                    <span class="countdown-label">Hours</span>
                </div>
                <div class="countdown-item">
                    <span class="countdown-number" id="minutes">00</span>
                    <span class="countdown-label">Min</span>
                </div>
                <div class="countdown-item">
                    <span class="countdown-number" id="seconds">00</span>
                    <span class="countdown-label">Sec</span>
                </div>
            </div>

            <div class="mb-3">
                <code class="bg-dark text-warning px-3 py-2 rounded">BLACKFRIDAY2025</code>
            </div>

            <a href="{{ route('register') }}" class="btn btn-accent btn-lg">
                <i class="fas fa-gift mr-2"></i> Claim 50% Off Now
            </a>
        </div>
    </section>
    @endif

    <!-- Pricing Section -->
    <section class="pricing-section section-padding">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Simple, Transparent Pricing</h2>
                <p class="section-subtitle">Choose the plan that fits your business needs</p>
            </div>

            @php
                $plans = \App\Models\Plan::active()->ordered()->get();
            @endphp

            <div class="row justify-content-center">
                @foreach($plans as $plan)
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="pricing-card {{ $plan->isProfessional() ? 'featured' : '' }}">
                        <h4 class="font-weight-bold">{{ $plan->name }}</h4>
                        <p class="text-muted">{{ $plan->description ?? 'Perfect for your needs' }}</p>

                        <div class="my-4">
                            @if($showBlackFriday ?? false)
                                <span class="price-original">${{ number_format($plan->price, 0) }}</span>
                                <span class="price">${{ number_format($plan->price * 0.5, 0) }}</span>
                            @else
                                <span class="price">{{ $plan->formatted_price }}</span>
                            @endif
                            <span class="price-period">/month</span>
                        </div>

                        <ul class="plan-features">
                            <li>
                                <i class="fas fa-check"></i>
                                <strong>{{ number_format($plan->invoice_limit) }}</strong> invoices/month
                            </li>
                            <li>
                                <i class="fas fa-check"></i>
                                AI-powered extraction
                            </li>
                            <li>
                                <i class="fas fa-check"></i>
                                REST API access
                            </li>
                            <li>
                                <i class="fas fa-check"></i>
                                Email support
                            </li>
                            @if($plan->isProfessional() || $plan->isEnterprise())
                            <li>
                                <i class="fas fa-check"></i>
                                Priority support
                            </li>
                            @endif
                            @if($plan->isEnterprise())
                            <li>
                                <i class="fas fa-check"></i>
                                Dedicated account manager
                            </li>
                            @endif
                        </ul>

                        <a href="{{ route('register', ['plan' => $plan->slug]) }}"
                           class="btn {{ $plan->isProfessional() ? 'btn-primary-gradient' : 'btn-outline-primary' }} btn-block">
                            Get Started
                        </a>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="text-center mt-4">
                <p class="text-muted">
                    <i class="fas fa-shield-alt mr-2"></i>
                    30-day money-back guarantee on all plans
                </p>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="section-padding">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">What Our Customers Say</h2>
                <p class="section-subtitle">Join hundreds of satisfied businesses</p>
            </div>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="testimonial-card">
                        <div class="stars mb-3">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="testimonial-text">
                            "InvoiceAI has transformed our accounting workflow. What used to take hours now takes minutes. The accuracy is incredible!"
                        </p>
                        <div class="testimonial-author">
                            <div class="testimonial-avatar bg-primary d-flex align-items-center justify-content-center text-white font-weight-bold" style="width: 50px; height: 50px; border-radius: 50%;">
                                MK
                            </div>
                            <div class="ml-3">
                                <div class="testimonial-name">Maria K.</div>
                                <div class="testimonial-role">CFO, TechStart GR</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="testimonial-card">
                        <div class="stars mb-3">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="testimonial-text">
                            "The Soft1 integration works flawlessly. We process 200+ invoices monthly and the data syncs perfectly every time."
                        </p>
                        <div class="testimonial-author">
                            <div class="testimonial-avatar bg-success d-flex align-items-center justify-content-center text-white font-weight-bold" style="width: 50px; height: 50px; border-radius: 50%;">
                                GP
                            </div>
                            <div class="ml-3">
                                <div class="testimonial-name">George P.</div>
                                <div class="testimonial-role">Operations Manager, LogiTrans</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="testimonial-card">
                        <div class="stars mb-3">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="testimonial-text">
                            "As a developer, the API is a dream to work with. Clean documentation, fast responses, and excellent support team."
                        </p>
                        <div class="testimonial-author">
                            <div class="testimonial-avatar bg-info d-flex align-items-center justify-content-center text-white font-weight-bold" style="width: 50px; height: 50px; border-radius: 50%;">
                                AN
                            </div>
                            <div class="ml-3">
                                <div class="testimonial-name">Alex N.</div>
                                <div class="testimonial-role">Senior Developer, DevHouse</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="section-padding" style="background: var(--bg-light);">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Frequently Asked Questions</h2>
                <p class="section-subtitle">Got questions? We've got answers</p>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            What file formats do you support?
                            <i class="fas fa-chevron-down faq-icon"></i>
                        </div>
                        <div class="faq-answer">
                            We support PDF, JPG, and PNG formats. Our AI can extract data from scanned documents, photos of invoices, and digital PDFs with equal accuracy.
                        </div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            How accurate is the data extraction?
                            <i class="fas fa-chevron-down faq-icon"></i>
                        </div>
                        <div class="faq-answer">
                            Our AI achieves 99.5% accuracy on standard invoice formats. The system continuously learns and improves from each processed document.
                        </div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            How does ERP integration work?
                            <i class="fas fa-chevron-down faq-icon"></i>
                        </div>
                        <div class="faq-answer">
                            We provide a REST API that can connect to any ERP system. For popular Greek ERPs like Soft1, Pylon, and Galaxy, we have pre-built connectors that make setup even easier.
                        </div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            Is my data secure?
                            <i class="fas fa-chevron-down faq-icon"></i>
                        </div>
                        <div class="faq-answer">
                            Absolutely. We use bank-level encryption (AES-256) for all data. Your invoices are processed securely and we're fully GDPR compliant. Data can be automatically deleted after processing if requested.
                        </div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            Can I try before I buy?
                            <i class="fas fa-chevron-down faq-icon"></i>
                        </div>
                        <div class="faq-answer">
                            Yes! Start with our free trial - no credit card required. You'll get full access to test the platform with your own invoices before committing.
                        </div>
                    </div>
                    @if($showBlackFriday ?? false)
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            How does the Black Friday offer work?
                            <i class="fas fa-chevron-down faq-icon"></i>
                        </div>
                        <div class="faq-answer">
                            Use code BLACKFRIDAY2025 at checkout to get 50% off any annual plan. The discount applies to the first year and the offer is valid until November 30, 2025.
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <!-- Final CTA -->
    <section class="final-cta">
        <div class="container text-center text-white">
            <h2 class="mb-4">Ready to Automate Your Invoice Processing?</h2>
            <p class="mb-4" style="opacity: 0.9; max-width: 600px; margin: 0 auto;">
                Join 500+ businesses that save hours every week with AI-powered invoice extraction.
            </p>
            <a href="{{ route('register') }}" class="btn btn-light btn-lg">
                Start Your Free Trial
            </a>
            <p class="mt-3" style="opacity: 0.7;">
                <small>No credit card required. Setup in 5 minutes.</small>
            </p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="landing-footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <img src="{{ asset('images/logo-white.svg') }}" alt="InvoiceAI" class="footer-logo">
                    <p>AI-powered invoice data extraction for modern businesses. Save time, reduce errors, grow faster.</p>
                    <div class="mt-3">
                        <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 mb-4">
                    <h6 class="footer-heading">Product</h6>
                    <a href="#how-it-works" class="footer-link">How It Works</a>
                    <a href="#" class="footer-link">Features</a>
                    <a href="#" class="footer-link">Pricing</a>
                    @auth
                    <a href="{{ route('api.documentation') }}" class="footer-link">API Docs</a>
                    @endauth
                </div>
                <div class="col-lg-2 col-md-4 mb-4">
                    <h6 class="footer-heading">Integrations</h6>
                    <a href="#" class="footer-link">Soft1</a>
                    <a href="#" class="footer-link">Pylon</a>
                    <a href="#" class="footer-link">Galaxy</a>
                    <a href="#" class="footer-link">More ERPs</a>
                </div>
                <div class="col-lg-2 col-md-4 mb-4">
                    <h6 class="footer-heading">Company</h6>
                    <a href="#" class="footer-link">About Us</a>
                    <a href="#" class="footer-link">Contact</a>
                    <a href="#" class="footer-link">Blog</a>
                    <a href="#" class="footer-link">Careers</a>
                </div>
                <div class="col-lg-2 mb-4">
                    <h6 class="footer-heading">Legal</h6>
                    <a href="#" class="footer-link">Privacy Policy</a>
                    <a href="#" class="footer-link">Terms of Service</a>
                    <a href="#" class="footer-link">Cookie Policy</a>
                </div>
            </div>
            <hr style="border-color: rgba(255,255,255,0.1);">
            <div class="text-center">
                <small>&copy; {{ date('Y') }} InvoiceAI. All rights reserved.</small>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <script>
        // FAQ Toggle
        function toggleFaq(element) {
            const item = element.parentElement;
            item.classList.toggle('active');
        }

        // Countdown Timer
        @if($showBlackFriday ?? false)
        const endDate = new Date('2025-11-30T23:59:59').getTime();

        function updateCountdown() {
            const now = new Date().getTime();
            const distance = endDate - now;

            if (distance < 0) {
                document.getElementById('countdown').innerHTML = '<p class="text-white">Offer Expired</p>';
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            document.getElementById('days').textContent = days.toString().padStart(2, '0');
            document.getElementById('hours').textContent = hours.toString().padStart(2, '0');
            document.getElementById('minutes').textContent = minutes.toString().padStart(2, '0');
            document.getElementById('seconds').textContent = seconds.toString().padStart(2, '0');
        }

        updateCountdown();
        setInterval(updateCountdown, 1000);
        @endif

        // Fade in animation on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.fade-in').forEach(el => {
            observer.observe(el);
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
