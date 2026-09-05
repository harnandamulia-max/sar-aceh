<script>
(function () {
    try {
        var saved = localStorage.getItem('sar-theme');
        var wantDark = saved
            ? saved === 'dark'
            : (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
        if (wantDark) {
            document.documentElement.setAttribute('data-theme', 'dark');
        }
    } catch (e) {}
})();
</script>
