<script>
    // Mobile: edge-swipe right opens the hamburger nav, swipe left closes it.
    // Drives Filament's own Alpine sidebar store, so it stays in sync with the button.
    (function () {
        let startX = 0, startY = 0;
        const EDGE = 40, THRESHOLD = 55, MOBILE = 1024;

        document.addEventListener('touchstart', (e) => {
            if (e.touches.length > 1) return;
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
        }, { passive: true });

        document.addEventListener('touchend', (e) => {
            if (window.innerWidth >= MOBILE) return;

            const t = e.changedTouches[0];
            const dx = t.clientX - startX, dy = t.clientY - startY;

            // decisive horizontal swipe only — never steal a scroll gesture
            if (Math.abs(dx) < THRESHOLD || Math.abs(dx) < Math.abs(dy) * 1.5) return;

            const store = window.Alpine?.store('sidebar');
            if (! store) return;

            if (dx > 0 && startX <= EDGE) store.open();
            else if (dx < 0) store.close();
        }, { passive: true });
    })();
</script>
