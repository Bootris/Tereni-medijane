{{-- Fullscreen photo viewer shared by the map cards and the court gallery.
     Arrows + keyboard ←/→ + touch swipe; Esc/backdrop/✕ closes.
     Pages open it via window.tereniGallery.open(urls, startIndex, title). --}}
<div id="glb" role="dialog" aria-modal="true" aria-label="Pregled fotografija terena"
    style="display:none;position:fixed;inset:0;z-index:70;background:rgba(15,23,42,.92);align-items:center;justify-content:center;padding:1rem">
    <figure style="margin:0;width:min(960px,94vw);display:flex;flex-direction:column;gap:.55rem">
        <img id="glb-img" src="" alt="Fotografija terena"
            style="width:100%;max-height:78vh;object-fit:contain;border-radius:.6rem;background:#0f172a">
        <figcaption style="display:flex;justify-content:space-between;gap:1rem;align-items:center;color:#e2e8f0;font-size:.92rem">
            <span id="glb-title" style="font-weight:600"></span>
            <span id="glb-count" style="flex:0 0 auto"></span>
        </figcaption>
    </figure>
    <button id="glb-prev" type="button" aria-label="Prethodna fotografija"
        style="position:fixed;left:.75rem;top:50%;transform:translateY(-50%);width:2.75rem;height:2.75rem;border:0;border-radius:999px;background:rgba(255,255,255,.92);font-size:1.6rem;line-height:1;cursor:pointer">‹</button>
    <button id="glb-next" type="button" aria-label="Sledeća fotografija"
        style="position:fixed;right:.75rem;top:50%;transform:translateY(-50%);width:2.75rem;height:2.75rem;border:0;border-radius:999px;background:rgba(255,255,255,.92);font-size:1.6rem;line-height:1;cursor:pointer">›</button>
    <button id="glb-close" type="button" aria-label="Zatvori pregled"
        style="position:fixed;top:.9rem;right:1rem;width:2.5rem;height:2.5rem;border:0;border-radius:999px;background:#fff;font-size:1.15rem;cursor:pointer">✕</button>
</div>

<script>
    (function () {
        const lb = document.getElementById('glb');
        const img = document.getElementById('glb-img');
        const title = document.getElementById('glb-title');
        const count = document.getElementById('glb-count');
        const prev = document.getElementById('glb-prev');
        const next = document.getElementById('glb-next');
        let items = [];
        let idx = 0;

        function show(i) {
            idx = (i + items.length) % items.length;
            img.src = items[idx];
            count.textContent = items.length > 1 ? (idx + 1) + ' / ' + items.length : '';
            prev.style.display = next.style.display = items.length > 1 ? '' : 'none';
        }

        function open(images, start, label) {
            if (!images || !images.length) return;
            items = images;
            title.textContent = label || '';
            show(start || 0);
            lb.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function close() {
            lb.style.display = 'none';
            img.src = '';
            document.body.style.overflow = '';
        }

        prev.addEventListener('click', () => show(idx - 1));
        next.addEventListener('click', () => show(idx + 1));
        document.getElementById('glb-close').addEventListener('click', close);
        lb.addEventListener('click', (e) => { if (e.target === lb) close(); });

        document.addEventListener('keydown', (e) => {
            if (lb.style.display !== 'flex') return;
            if (e.key === 'Escape') close();
            if (e.key === 'ArrowLeft') show(idx - 1);
            if (e.key === 'ArrowRight') show(idx + 1);
        });

        // Swipe left/right on the photo — QR reporting is phone-first.
        let touchX = null;
        lb.addEventListener('touchstart', (e) => { touchX = e.changedTouches[0].clientX; }, { passive: true });
        lb.addEventListener('touchend', (e) => {
            if (touchX === null) return;
            const dx = e.changedTouches[0].clientX - touchX;
            touchX = null;
            if (Math.abs(dx) > 40) show(dx < 0 ? idx + 1 : idx - 1);
        }, { passive: true });

        window.tereniGallery = { open };

        // Court-card covers carry their gallery inline — bind them here so
        // every page that includes this component gets the viewer for free.
        document.querySelectorAll('.court-cover[data-photos]').forEach((cover) => {
            cover.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                open(JSON.parse(cover.dataset.photos), 0, cover.dataset.title || '');
            });
        });
    })();
</script>
