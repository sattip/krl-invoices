<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.seo-head', ['title' => __('landing.title'), 'description' => __('landing.description')])

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">

    <!-- Styles (local only; no third-party font requests) -->
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
    <style>
        .landing-nav .nav-actions { display:flex; align-items:center; flex-wrap:wrap; gap:8px; justify-content:flex-end; }
        .landing-nav .lang-link { color:#fff; opacity:.9; font-size:14px; text-decoration:underline; margin-right:6px; }
        @media (max-width: 575.98px) {
            .landing-nav .nav-logo img { height:30px; }
            .landing-nav .btn { padding:.3rem .65rem; font-size:.85rem; }
            .hero-section h1.display-4 { font-size:2.1rem; }
        }
    </style>
</head>
<body>
    @php
        $legal = config('legal.company');
        $vatMode = config('legal.pricing.vat_mode');
    @endphp
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <!-- Navigation -->
            <nav class="landing-nav">
                <div class="container">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ lroute('home') }}" class="nav-logo">
                            <img src="{{ asset('images/logo-white.svg') }}" alt="InvoiceAI" height="40">
                        </a>
                        <div class="nav-actions">
                            @include('partials.lang-switch', ['class' => 'lang-link'])
                            @auth
                                <a href="{{ route('dashboard') }}" class="btn btn-outline-white">{{ __('landing.nav_dashboard') }}</a>
                            @else
                                <a href="{{ route('login') }}" class="btn btn-outline-white">{{ __('landing.nav_login') }}</a>
                                <a href="{{ route('register') }}" class="btn btn-light">{{ __('landing.nav_register') }}</a>
                            @endauth
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Hero Content -->
            <div class="container py-5">
                <div class="row align-items-center">
                    <div class="col-lg-6 text-white">
                        <h1 class="display-4 font-weight-bold mb-4">{!! __('landing.hero_title') !!}</h1>
                        <p class="lead mb-4" style="opacity: 0.9;">{{ __('landing.hero_text') }}</p>
                        <div class="mb-4">
                            <a href="#pricing" class="btn btn-accent btn-lg mr-3 mb-2">{{ __('landing.cta_plans') }}</a>
                            <a href="#how-it-works" class="btn btn-outline-white btn-lg mb-2">
                                <i class="fas fa-play-circle mr-2"></i> {{ __('landing.cta_how') }}
                            </a>
                        </div>
                        <div class="d-flex align-items-center flex-wrap" style="opacity: 0.8;">
                            <span class="mr-3 mb-2"><i class="fas fa-check-circle mr-1"></i> {{ __('landing.chip_formats') }}</span>
                            <span class="mr-3 mb-2"><i class="fas fa-check-circle mr-1"></i> {{ __('landing.chip_api') }}</span>
                            <span class="mb-2"><i class="fas fa-check-circle mr-1"></i> {{ __('landing.chip_monthly') }}</span>
                        </div>
                    </div>
                    <div class="col-lg-6 mt-5 mt-lg-0">
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
                    <small class="text-uppercase font-weight-bold">{{ __('landing.formats') }}</small>
                    <div class="mt-2">
                        <span class="badge badge-light mr-2"><i class="fas fa-file-pdf mr-1"></i> PDF</span>
                        <span class="badge badge-light mr-2"><i class="fas fa-file-image mr-1"></i> JPG</span>
                        <span class="badge badge-light"><i class="fas fa-file-image mr-1"></i> PNG</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section id="how-it-works" class="section-padding">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">{{ __('landing.how_title') }}</h2>
                <p class="section-subtitle">{{ __('landing.how_sub') }}</p>
            </div>
            <div class="row">
                @foreach([[1, 'fa-cloud-upload-alt'], [2, 'fa-robot'], [3, 'fa-clipboard-check']] as [$n, $icon])
                <div class="col-md-4">
                    <div class="step-card fade-in">
                        <div class="step-number">{{ $n }}</div>
                        <i class="fas {{ $icon }} step-icon"></i>
                        <h4>{{ __('landing.step'.$n.'_t') }}</h4>
                        <p class="text-muted">{{ __('landing.step'.$n.'_d') }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Integrations (truthful: generic API, no native connectors) -->
    <section class="erp-section section-padding">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">{{ __('landing.int_title') }}</h2>
                <p class="section-subtitle">{{ __('landing.int_sub') }}</p>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="feature-card">
                        <ul class="mb-0 text-muted" style="padding-left: 1.1rem;">
                            <li class="mb-2">{{ __('landing.int_1') }}</li>
                            <li class="mb-2">{{ __('landing.int_2') }}</li>
                            <li>{{ __('landing.int_3') }} <a href="mailto:{{ $legal['info_email'] }}">{{ $legal['info_email'] }}</a>.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Grid -->
    <section class="section-padding">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">{{ __('landing.feat_title') }}</h2>
                <p class="section-subtitle">{{ __('landing.feat_sub') }}</p>
            </div>
            <div class="row">
                @foreach([1 => 'fa-brain', 2 => 'fa-table', 3 => 'fa-code', 4 => 'fa-layer-group', 5 => 'fa-users', 6 => 'fa-chart-pie'] as $n => $icon)
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas {{ $icon }}"></i></div>
                        <h5>{{ __('landing.f'.$n.'_t') }}</h5>
                        <p class="text-muted">{{ __('landing.f'.$n.'_d') }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            <p class="text-center text-muted small mb-0">
                <i class="fas fa-info-circle mr-1"></i> {{ __('landing.disclaimer') }}
            </p>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="pricing-section section-padding">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">{{ __('landing.pricing_title') }}</h2>
                <p class="section-subtitle">{{ __('landing.pricing_sub') }}</p>
            </div>

            @php
                $plans = \App\Models\Plan::active()->ordered()->get();
            @endphp

            <div class="row justify-content-center">
                @foreach($plans as $plan)
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="pricing-card {{ $plan->isProfessional() ? 'featured' : '' }}" @if($plan->isProfessional()) data-badge="{{ __('landing.badge') }}" @endif>
                        <h4 class="font-weight-bold">{{ $plan->name }}</h4>
                        <p class="text-muted">{{ \Illuminate\Support\Facades\Lang::has('landing.blurb_'.$plan->slug) ? __('landing.blurb_'.$plan->slug) : $plan->description }}</p>

                        <div class="my-4">
                            <span class="price">{{ $plan->formatted_price }}</span>
                            <span class="price-period">{{ __('landing.per_month') }}</span>
                            @if(in_array($vatMode, ['included', 'excluded'], true))
                                <div class="small text-muted mt-1" data-vat-mode="{{ $vatMode }}">{{ __('landing.vat_'.$vatMode) }}</div>
                            @endif
                        </div>

                        <ul class="plan-features">
                            <li><i class="fas fa-check"></i> <span>{!! __('landing.pf_limit', ['count' => '<strong>'.e(number_el($plan->invoice_limit)).'</strong>']) !!}</span></li>
                            <li><i class="fas fa-check"></i> {{ __('landing.pf_ai') }}</li>
                            <li><i class="fas fa-check"></i> {{ __('landing.pf_api') }}</li>
                            <li><i class="fas fa-check"></i> {{ __('landing.pf_support') }}</li>
                        </ul>

                        <a href="{{ route('register', ['plan' => $plan->slug]) }}"
                           class="btn {{ $plan->isProfessional() ? 'btn-primary-gradient' : 'btn-outline-primary' }} btn-block">
                            {{ __('landing.choose_plan') }}
                        </a>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="text-center mt-4">
                @if(config('subscription.trial.enabled'))
                    <p class="mb-2" data-trial-note>
                        {{ __('landing.trial_note', ['days' => (int) config('subscription.trial.days'), 'count' => (int) config('subscription.trial.invoice_limit')]) }}
                        <a href="{{ route('register') }}">{{ __('landing.trial_cta') }}</a>
                    </p>
                @endif
                <p class="text-muted small mb-0">
                    {{ __('landing.pricing_note') }} <a href="{{ lroute('legal.terms') }}">{{ __('landing.terms') }}</a>
                </p>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="section-padding" style="background: var(--bg-light);">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">{{ __('landing.faq_title') }}</h2>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    @foreach(__('landing.faqs') as [$q, $a])
                    <div class="faq-item">
                        <div class="faq-question" role="button" tabindex="0" onclick="toggleFaq(this)" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();toggleFaq(this);}">
                            {{ $q }}
                            <i class="fas fa-chevron-down faq-icon"></i>
                        </div>
                        <div class="faq-answer">{{ str_replace(':email', $legal['support_email'], $a) }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- Final CTA -->
    <section class="final-cta">
        <div class="container text-center text-white">
            <h2 class="mb-4">{{ __('landing.final_title') }}</h2>
            <p class="mb-4" style="opacity: 0.9; max-width: 600px; margin: 0 auto;">{{ __('landing.final_text') }}</p>
            <a href="{{ route('register') }}" class="btn btn-light btn-lg">{{ __('landing.final_cta') }}</a>
            <p class="mt-3" style="opacity: 0.8;">
                <small>{{ __('landing.final_q') }} <a href="mailto:{{ $legal['info_email'] }}" class="text-white"><u>{{ $legal['info_email'] }}</u></a></small>
            </p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="landing-footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-5 mb-4">
                    <img src="{{ asset('images/logo-white.svg') }}" alt="InvoiceAI" class="footer-logo">
                    <p>{{ __('landing.footer_about') }}<br>
                        InvoiceAI {{ __('landing.footer_by') }} <a href="https://obstechnologies.gr" class="footer-link d-inline" rel="noopener" target="_blank">OBS Technologies</a></p>
                </div>
                <div class="col-lg-2 col-md-4 mb-4">
                    <h6 class="footer-heading">{{ __('landing.footer_service') }}</h6>
                    <a href="#how-it-works" class="footer-link">{{ __('landing.cta_how') }}</a>
                    <a href="#pricing" class="footer-link">{{ __('landing.footer_pricing') }}</a>
                    @auth
                    <a href="{{ route('api.documentation') }}" class="footer-link">{{ __('landing.footer_api') }}</a>
                    @endauth
                    <a href="{{ route('login') }}" class="footer-link">{{ __('landing.nav_login') }}</a>
                </div>
                <div class="col-lg-2 col-md-4 mb-4">
                    <h6 class="footer-heading">{{ __('landing.footer_legal') }}</h6>
                    <a href="{{ lroute('legal.terms') }}" class="footer-link">{{ __('landing.terms') }}</a>
                    <a href="{{ lroute('legal.privacy') }}" class="footer-link">{{ __('landing.privacy') }}</a>
                    <a href="{{ lroute('legal.cookies') }}" class="footer-link">{{ __('landing.cookies') }}</a>
                </div>
                <div class="col-lg-3 col-md-4 mb-4">
                    <h6 class="footer-heading">{{ __('landing.footer_contact') }}</h6>
                    <a href="mailto:{{ $legal['support_email'] }}" class="footer-link">{{ $legal['support_email'] }}</a>
                    <a href="mailto:{{ $legal['info_email'] }}" class="footer-link">{{ $legal['info_email'] }}</a>
                    <a href="{{ lroute('legal.contact') }}" class="footer-link">{{ __('landing.footer_company') }}</a>
                    @include('partials.lang-switch', ['class' => 'footer-link'])
                </div>
            </div>
            <hr style="border-color: rgba(255,255,255,0.1);">
            <div class="text-center">
                <small>&copy; {{ date('Y') }} InvoiceAI {{ __('landing.footer_by') }} <a href="https://obstechnologies.gr" rel="noopener" target="_blank" class="text-white"><u>OBS Technologies</u></a>
                    · {{ $legal['name'] }} · {{ __('landing.vat_label') }} {{ app()->getLocale() === 'en' ? $legal['vat_eu'] : $legal['vat'] }} · {{ __('landing.gemi_label') }} {{ $legal['gemi'] }} · {{ $legal['address'] }}</small>
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
