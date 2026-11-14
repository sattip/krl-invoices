<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - InvoiceAI</title>
    <meta name="description" content="@yield('description', 'InvoiceAI από την OBS TECHNOLOGIES Ε.Ε.')">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">
    <style>
        :root { --ink:#111827; --muted:#4b5563; --line:#e5e7eb; --brand:#4F46E5; }
        * { box-sizing: border-box; }
        body { margin:0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; color:var(--ink); background:#f9fafb; line-height:1.65; }
        header.legal-top { background:linear-gradient(135deg,#4F46E5,#7C3AED); padding:14px 20px; }
        header.legal-top .wrap { max-width:880px; margin:0 auto; display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; }
        header.legal-top a { color:#fff; text-decoration:none; font-size:14px; }
        main.legal { max-width:880px; margin:28px auto; background:#fff; border:1px solid var(--line); border-radius:12px; padding:28px clamp(18px,4vw,44px); }
        main.legal h1 { font-size:clamp(24px,3.2vw,32px); line-height:1.25; margin:0 0 6px; }
        main.legal h2 { font-size:20px; margin:30px 0 8px; padding-top:6px; border-top:1px solid var(--line); }
        main.legal h3 { font-size:16.5px; margin:20px 0 6px; }
        main.legal p, main.legal li { color:#1f2937; font-size:15.5px; overflow-wrap:anywhere; }
        main.legal .updated { color:var(--muted); font-size:14px; margin-bottom:18px; }
        main.legal .note { background:#fffbeb; border:1px solid #fde68a; border-radius:8px; padding:12px 14px; }
        main.legal table { width:100%; border-collapse:collapse; margin:10px 0; font-size:14.5px; display:block; overflow-x:auto; }
        main.legal th, main.legal td { border:1px solid var(--line); padding:8px 10px; text-align:left; vertical-align:top; min-width:120px; }
        main.legal th { background:#f3f4f6; }
        main.legal a { color:var(--brand); }
        main.legal nav.toc { font-size:14.5px; background:#f9fafb; border:1px solid var(--line); border-radius:8px; padding:10px 16px; }
    </style>
</head>
<body>
    <header class="legal-top">
        <div class="wrap">
            <a href="{{ route('home') }}"><img src="{{ asset('images/logo-white.svg') }}" alt="InvoiceAI" height="32" style="display:block;"></a>
            <span>
                @auth
                    <a href="{{ route('dashboard') }}">Πίνακας ελέγχου</a>
                @else
                    <a href="{{ route('login') }}" style="margin-right:14px;">Σύνδεση</a>
                    <a href="{{ route('register') }}">Εγγραφή</a>
                @endauth
            </span>
        </div>
    </header>

    <main class="legal">
        @yield('content')
    </main>

    @include('partials.legal-links', ['variant' => 'legal'])
</body>
</html>
