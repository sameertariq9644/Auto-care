{{-- Applies the saved light/dark preference before first paint (falls back to the page's native theme). --}}
<script>
    (function () {
        var root = document.documentElement;
        var theme = root.dataset.native || 'dark';
        try { theme = localStorage.getItem('theme') || theme; } catch (e) {}
        root.classList.add(theme === 'light' ? 'light' : 'dark');
    })();
</script>
