{{-- Disisipkan ke halaman publik hanya saat pratinjau bagian di admin: menyisakan satu bagian dan menonaktifkan interaksi. --}}
<style>
    body { pointer-events: none !important; }
    html { scroll-behavior: auto !important; }
</style>
<script>
    (function () {
        var key = @json($section);
        var targets = Array.prototype.slice.call(document.querySelectorAll('[data-cms-section="' + key + '"]'));

        if (!targets.length) {
            document.body.innerHTML = '<p style="padding:48px;font:16px system-ui,sans-serif;color:#64748b">Bagian ini tidak tampil di halaman.</p>';

            return;
        }

        // Pertahankan target beserta seluruh induknya; sembunyikan saudara-saudara lainnya di tiap tingkat.
        var keep = new Set();

        targets.forEach(function (target) {
            for (var node = target; node && node !== document.documentElement; node = node.parentElement) {
                keep.add(node);
            }
        });

        keep.forEach(function (node) {
            if (targets.indexOf(node) !== -1) return;

            Array.prototype.forEach.call(node.children, function (child) {
                if (!keep.has(child) && !/^(SCRIPT|STYLE|LINK|TEMPLATE)$/.test(child.tagName)) {
                    child.style.display = 'none';
                }
            });
        });

        // Bagian bersarang (mis. CTA di dalam "Tujuan perusahaan") tidak ikut tampil.
        targets.forEach(function (target) {
            Array.prototype.forEach.call(target.querySelectorAll('section[data-cms-section]'), function (nested) {
                nested.style.display = 'none';
            });
        });
    })();
</script>
