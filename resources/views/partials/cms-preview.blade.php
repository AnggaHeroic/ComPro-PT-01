{{-- Hanya dimuat di mode pratinjau admin (?cms-preview): bagian halaman menjadi bisa diklik dan memberi tahu form admin di halaman induk. --}}
<style>
    [data-cms-section]:not(header) { position: relative; }
    [data-cms-hover] { outline: 3px solid #cb1e1b; outline-offset: -3px; cursor: pointer; }
    [data-cms-hover]::after {
        content: 'Edit: ' attr(data-cms-label);
        position: absolute; top: 8px; left: 8px; z-index: 70;
        background: #cb1e1b; color: #fff; padding: 4px 10px; border-radius: 4px;
        font: 600 12px/1.4 system-ui, sans-serif; pointer-events: none; white-space: nowrap;
    }
    [data-cms-active] { outline: 3px dashed #cb1e1b; outline-offset: -3px; }
    [data-cms-active]:not([data-cms-hover])::before {
        content: attr(data-cms-label);
        position: absolute; top: 8px; left: 8px; z-index: 70;
        background: #1f2937; color: #fff; padding: 4px 10px; border-radius: 4px;
        font: 600 12px/1.4 system-ui, sans-serif; pointer-events: none; white-space: nowrap;
    }
    [data-cms-section="identity"][data-cms-hover]::after { top: 4px; }
    html { scroll-behavior: auto !important; }
</style>
<script>
    (function () {
        var origin = window.location.origin;
        var hover = null;
        var active = null;

        function nearest(node) {
            return node && node.closest ? node.closest('[data-cms-section]') : null;
        }

        document.addEventListener('mouseover', function (event) {
            var target = nearest(event.target);

            if (target === hover) return;
            if (hover) hover.removeAttribute('data-cms-hover');
            hover = target;
            if (target) target.setAttribute('data-cms-hover', '');
        }, true);

        // Tautan dan tombol tidak boleh berjalan di pratinjau; klik hanya memilih bagian.
        document.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();

            var target = nearest(event.target);

            if (target) {
                window.parent.postMessage({ type: 'cms-select', section: target.getAttribute('data-cms-section') }, origin);
            }
        }, true);

        document.addEventListener('submit', function (event) { event.preventDefault(); }, true);

        window.addEventListener('message', function (event) {
            if (event.origin !== origin || !event.data || event.data.type !== 'cms-active') return;

            if (active) active.removeAttribute('data-cms-active');
            active = document.querySelector('[data-cms-section="' + event.data.section + '"]');

            if (active) {
                active.setAttribute('data-cms-active', '');

                if (event.data.scroll) {
                    var top = active.getBoundingClientRect().top + window.scrollY - 90;
                    window.scrollTo({ top: Math.max(top, 0), behavior: 'smooth' });
                }
            }
        });

        window.parent.postMessage({ type: 'cms-ready' }, origin);
    })();
</script>
