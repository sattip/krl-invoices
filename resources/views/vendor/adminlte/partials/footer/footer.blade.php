<footer class="main-footer">
    @hasSection('footer')
        @yield('footer')
    @endif
    @include('partials.legal-links', ['variant' => 'app'])
</footer>