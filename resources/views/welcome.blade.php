<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="InvoiceAI: εξαγωγή δομημένων δεδομένων από τιμολόγια PDF, JPG και PNG με τεχνητή νοημοσύνη, μέσω web εφαρμογής και REST API. Υπηρεσία της OBS TECHNOLOGIES Ε.Ε.">

    <title>InvoiceAI - Εξαγωγή δεδομένων τιμολογίων με τεχνητή νοημοσύνη</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">


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
                                <a href="{{ route('dashboard') }}" class="btn btn-outline-white">Πίνακας ελέγχου</a>
                            @else
                                @if (Route::has('login'))
                                    <a href="{{ route('login') }}" class="btn btn-outline-white mr-2">Σύνδεση</a>
                                @endif
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="btn btn-light">Εγγραφή</a>
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
                            Δεδομένα τιμολογίων, <span style="color: #F59E0B;">χωρίς πληκτρολόγηση</span>
                        </h1>
                        <p class="lead mb-4" style="opacity: 0.9;">
                            Ανεβάστε τιμολόγια σε PDF ή εικόνα και λάβετε δομημένα δεδομένα: εκδότη, πελάτη, ΑΦΜ, γραμμές ειδών, ΦΠΑ και σύνολα.
                            Ελέγξτε τα στην εφαρμογή ή πάρτε τα σε JSON μέσω REST API για το δικό σας σύστημα.
                        </p>
                        <div class="mb-4">
                            <a href="#pricing" class="btn btn-accent btn-lg mr-3">
                                Δείτε τα πακέτα
                            </a>
                            <a href="#how-it-works" class="btn btn-outline-white btn-lg">
                                <i class="fas fa-play-circle mr-2"></i> Πώς λειτουργεί
                            </a>
                        </div>
                        <div class="d-flex align-items-center flex-wrap" style="opacity: 0.8;">
                            <span class="mr-3 mb-2"><i class="fas fa-check-circle mr-1"></i> PDF, JPG, PNG έως 10 MB</span>
                            <span class="mr-3 mb-2"><i class="fas fa-check-circle mr-1"></i> REST API με JSON</span>
                            <span class="mb-2"><i class="fas fa-check-circle mr-1"></i> Μηνιαία συνδρομή, ακύρωση όποτε θέλετε</span>
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
                    <small class="text-uppercase font-weight-bold">Υποστηριζόμενες μορφές</small>
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
                <h2 class="section-title">Πώς λειτουργεί</h2>
                <p class="section-subtitle">Τρία βήματα, από το αρχείο στα δομημένα δεδομένα</p>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="step-card fade-in">
                        <div class="step-number">1</div>
                        <i class="fas fa-cloud-upload-alt step-icon"></i>
                        <h4>Ανεβάζετε το τιμολόγιο</h4>
                        <p class="text-muted">PDF, JPG ή PNG, ένα ή περισσότερα μαζί, από την εφαρμογή ή μέσω API.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="step-card fade-in">
                        <div class="step-number">2</div>
                        <i class="fas fa-robot step-icon"></i>
                        <h4>Γίνεται η εξαγωγή</h4>
                        <p class="text-muted">Μοντέλο τεχνητής νοημοσύνης διαβάζει το έγγραφο και επιστρέφει τα πεδία του τιμολογίου.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="step-card fade-in">
                        <div class="step-number">3</div>
                        <i class="fas fa-clipboard-check step-icon"></i>
                        <h4>Ελέγχετε και αξιοποιείτε</h4>
                        <p class="text-muted">Ελέγχετε τα αποτελέσματα και τα μεταφέρετε στο σύστημά σας μέσω του REST API.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Integrations (truthful: generic API, no native connectors) -->
    <section class="erp-section section-padding">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Σύνδεση με το σύστημά σας</h2>
                <p class="section-subtitle">Τα δεδομένα διατίθενται μέσω REST API σε JSON, ώστε να τα παραλάβει το ERP ή το λογιστικό σας πρόγραμμα</p>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="feature-card">
                        <ul class="mb-0 text-muted" style="padding-left: 1.1rem;">
                            <li class="mb-2">Endpoints για ανέβασμα, λίστα, προβολή και διαγραφή τιμολογίων, με αυθεντικοποίηση μέσω API token.</li>
                            <li class="mb-2">Η σύνδεση με συγκεκριμένο ERP (π.χ. Soft1, Pylon, Galaxy, Epsilon) υλοποιείται μέσω του API από εσάς ή τον συνεργάτη σας· δεν περιλαμβάνονται έτοιμοι connectors.</li>
                            <li>Χρειάζεστε υλοποίηση διασύνδεσης; Η OBS Technologies αναλαμβάνει τέτοια έργα κατόπιν προσφοράς: <a href="mailto:{{ config('legal.company.info_email') }}">{{ config('legal.company.info_email') }}</a>.</li>
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
                <h2 class="section-title">Λειτουργίες</h2>
                <p class="section-subtitle">Ό,τι περιλαμβάνει σήμερα η υπηρεσία</p>
            </div>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-brain"></i></div>
                        <h5>Εξαγωγή με τεχνητή νοημοσύνη</h5>
                        <p class="text-muted">Εκδότης, πελάτης, ΑΦΜ, αριθμός και ημερομηνία, γραμμές ειδών, εκπτώσεις, λοιπές χρεώσεις, ΦΠΑ και σύνολα.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-table"></i></div>
                        <h5>Δομημένο αποτέλεσμα</h5>
                        <p class="text-muted">Προβολή στην εφαρμογή και JSON μέσω API, με σταθερά πεδία για εύκολη ενσωμάτωση.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-code"></i></div>
                        <h5>REST API</h5>
                        <p class="text-muted">API tokens ανά χρήστη και τεκμηρίωση μέσα στην εφαρμογή.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-layer-group"></i></div>
                        <h5>Πολλά αρχεία μαζί</h5>
                        <p class="text-muted">Ανεβάστε περισσότερα τιμολόγια σε μία κίνηση από την εφαρμογή.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-users"></i></div>
                        <h5>Λογαριασμός εταιρείας</h5>
                        <p class="text-muted">Τα τιμολόγια ανήκουν στην εταιρεία σας και είναι διαχωρισμένα από άλλους πελάτες.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-chart-pie"></i></div>
                        <h5>Έλεγχος χρήσης</h5>
                        <p class="text-muted">Βλέπετε πόσα τιμολόγια έχετε χρησιμοποιήσει από το μηνιαίο όριο του πακέτου σας.</p>
                    </div>
                </div>
            </div>
            <p class="text-center text-muted small mb-0">
                <i class="fas fa-info-circle mr-1"></i>
                Τα αποτελέσματα παράγονται αυτόματα και πρέπει να ελέγχονται πριν από λογιστική ή φορολογική χρήση. Η υπηρεσία δεν εκδίδει παραστατικά και δεν διαβιβάζει στο myDATA.
            </p>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="pricing-section section-padding">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Τιμές</h2>
                <p class="section-subtitle">Μηνιαία συνδρομή με όριο τιμολογίων ανά μήνα</p>
            </div>

            @php
                $plans = \App\Models\Plan::active()->ordered()->get();
                $planBlurbs = [
                    'starter' => 'Για μικρές επιχειρήσεις',
                    'professional' => 'Για επιχειρήσεις σε ανάπτυξη',
                    'enterprise' => 'Για μεγαλύτερο όγκο',
                ];
            @endphp

            <div class="row justify-content-center">
                @foreach($plans as $plan)
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="pricing-card {{ $plan->isProfessional() ? 'featured' : '' }}">
                        <h4 class="font-weight-bold">{{ $plan->name }}</h4>
                        <p class="text-muted">{{ $planBlurbs[$plan->slug] ?? $plan->description }}</p>

                        <div class="my-4">
                            <span class="price">{{ $plan->formatted_price }}</span>
                            <span class="price-period">/μήνα</span>
                        </div>

                        <ul class="plan-features">
                            <li><i class="fas fa-check"></i> <strong>{{ number_el($plan->invoice_limit) }}</strong> τιμολόγια/μήνα</li>
                            <li><i class="fas fa-check"></i> Εξαγωγή με τεχνητή νοημοσύνη</li>
                            <li><i class="fas fa-check"></i> Πρόσβαση στο REST API</li>
                            <li><i class="fas fa-check"></i> Υποστήριξη μέσω email</li>
                        </ul>

                        <a href="{{ route('register', ['plan' => $plan->slug]) }}"
                           class="btn {{ $plan->isProfessional() ? 'btn-primary-gradient' : 'btn-outline-primary' }} btn-block">
                            Επιλογή πακέτου
                        </a>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="text-center mt-4">
                <p class="text-muted small mb-0">
                    Πληρωμή με κάρτα μέσω Stripe· το τελικό ποσό εμφανίζεται πριν την επιβεβαίωση. Η συνδρομή ανανεώνεται μηνιαία και ακυρώνεται από τον λογαριασμό σας,
                    με ισχύ στο τέλος της περιόδου. <a href="{{ route('legal.terms') }}">Όροι Χρήσης</a>
                </p>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="section-padding" style="background: var(--bg-light);">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Συχνές ερωτήσεις</h2>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    @php
                        $faqs = [
                            ['Ποιες μορφές αρχείων υποστηρίζονται;', 'PDF, JPG και PNG, έως 10 MB ανά αρχείο. Μπορεί να είναι ψηφιακά PDF, σαρωμένα έγγραφα ή φωτογραφίες· η ποιότητα του αρχείου επηρεάζει το αποτέλεσμα.'],
                            ['Πόσο ακριβής είναι η εξαγωγή;', 'Εξαρτάται από την ποιότητα και τη μορφή του εγγράφου. Δεν υπόσχεται συγκεκριμένο ποσοστό ακρίβειας: ελέγχετε πάντα τα αποτελέσματα, ή αναθέστε τον έλεγχο στον λογιστή σας, πριν τα χρησιμοποιήσετε.'],
                            ['Συνδέεται με το ERP μου;', 'Μέσω του REST API, που επιστρέφει τα δεδομένα σε JSON. Δεν υπάρχουν έτοιμοι connectors για συγκεκριμένα ERP· η διασύνδεση υλοποιείται μέσω του API.'],
                            ['Πού επεξεργάζονται τα αρχεία μου;', 'Τα αρχεία και τα δεδομένα φιλοξενούνται σε διακομιστή στη Γερμανία. Για την εξαγωγή το περιεχόμενο αποστέλλεται στην Anthropic (ΗΠΑ). Λεπτομέρειες στην Πολιτική Απορρήτου.'],
                            ['Μπορώ να διαγράψω τα δεδομένα μου;', 'Ναι. Κάθε τιμολόγιο διαγράφεται μαζί με το αρχείο του από τη λίστα τιμολογίων. Για πλήρη διαγραφή της εταιρείας σας στείλτε email στο ' . config('legal.company.support_email') . '.'],
                            ['Πώς γίνεται η χρέωση;', 'Με μηνιαία συνδρομή μέσω Stripe. Ακυρώνετε από τη σελίδα «Χρεώσεις & χρήση» και η πρόσβαση συνεχίζεται ως το τέλος της πληρωμένης περιόδου.'],
                        ];
                    @endphp
                    @foreach($faqs as [$q, $a])
                    <div class="faq-item">
                        <div class="faq-question" role="button" tabindex="0" onclick="toggleFaq(this)" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();toggleFaq(this);}">
                            {{ $q }}
                            <i class="fas fa-chevron-down faq-icon"></i>
                        </div>
                        <div class="faq-answer">{{ $a }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- Final CTA -->
    <section class="final-cta">
        <div class="container text-center text-white">
            <h2 class="mb-4">Λιγότερη πληκτρολόγηση στα τιμολόγια</h2>
            <p class="mb-4" style="opacity: 0.9; max-width: 600px; margin: 0 auto;">
                Επιλέξτε πακέτο, ανεβάστε τα πρώτα σας τιμολόγια και ελέγξτε το αποτέλεσμα.
            </p>
            <a href="{{ route('register') }}" class="btn btn-light btn-lg">Δημιουργία λογαριασμού</a>
            <p class="mt-3" style="opacity: 0.8;">
                <small>Απορίες; <a href="mailto:{{ config('legal.company.info_email') }}" class="text-white"><u>{{ config('legal.company.info_email') }}</u></a></small>
            </p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="landing-footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-5 mb-4">
                    <img src="{{ asset('images/logo-white.svg') }}" alt="InvoiceAI" class="footer-logo">
                    <p>Εξαγωγή δεδομένων τιμολογίων με τεχνητή νοημοσύνη. Μια υπηρεσία της
                        <a href="{{ config('legal.company.website') }}" class="footer-link d-inline" rel="noopener" target="_blank">OBS Technologies</a>.</p>
                </div>
                <div class="col-lg-2 col-md-4 mb-4">
                    <h6 class="footer-heading">Υπηρεσία</h6>
                    <a href="#how-it-works" class="footer-link">Πώς λειτουργεί</a>
                    <a href="#pricing" class="footer-link">Τιμές</a>
                    @auth
                    <a href="{{ route('api.documentation') }}" class="footer-link">Τεκμηρίωση API</a>
                    @endauth
                    <a href="{{ route('login') }}" class="footer-link">Σύνδεση</a>
                </div>
                <div class="col-lg-2 col-md-4 mb-4">
                    <h6 class="footer-heading">Νομικά</h6>
                    <a href="{{ route('legal.terms') }}" class="footer-link">Όροι Χρήσης</a>
                    <a href="{{ route('legal.privacy') }}" class="footer-link">Πολιτική Απορρήτου</a>
                    <a href="{{ route('legal.cookies') }}" class="footer-link">Πολιτική Cookies</a>
                </div>
                <div class="col-lg-3 col-md-4 mb-4">
                    <h6 class="footer-heading">Επικοινωνία</h6>
                    <a href="mailto:{{ config('legal.company.support_email') }}" class="footer-link">{{ config('legal.company.support_email') }}</a>
                    <a href="mailto:{{ config('legal.company.info_email') }}" class="footer-link">{{ config('legal.company.info_email') }}</a>
                    <a href="{{ route('legal.contact') }}" class="footer-link">Στοιχεία εταιρείας</a>
                </div>
            </div>
            <hr style="border-color: rgba(255,255,255,0.1);">
            <div class="text-center">
                <small>&copy; {{ date('Y') }} InvoiceAI · {{ config('legal.company.name') }} · ΑΦΜ {{ config('legal.company.vat') }} · ΓΕΜΗ {{ config('legal.company.gemi') }} · {{ config('legal.company.address') }}</small>
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
