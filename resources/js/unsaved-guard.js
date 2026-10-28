/**
 * Peringatan perubahan yang belum disimpan.
 *
 * Form admin yang diberi atribut data-unsaved-guard dipantau: isiannya dibandingkan dengan keadaan saat
 * halaman dimuat. Bila ada yang berubah dan admin hendak meninggalkan halaman,
 *  - klik tautan di dalam situs memunculkan dialog "Perubahan belum disimpan" (Tetap di halaman / Keluar
 *    tanpa menyimpan), dan
 *  - menutup tab, memuat ulang, atau tombol kembali browser memunculkan peringatan bawaan browser
 *    (teksnya tidak bisa diubah oleh situs).
 * Menyimpan form itu sendiri, atau keluar otomatis karena tidak aktif, tidak diperingatkan.
 *
 * Atribut tambahan:
 *   data-unsaved-dirty    form dianggap sudah berubah sejak awal (mis. dimuat ulang setelah galat validasi,
 *                         isiannya belum tersimpan)
 *   data-unsaved-ignore   pada elemen/pembungkus: isiannya tidak dihitung (mis. penanda bagian aktif)
 *   data-unsaved-skip     pada tautan: tidak diperingatkan (mis. tombol yang memang membuang perubahan)
 */
const IGNORED_NAMES = ['_token', '_method'];
const POLL_MS = 600;

export function setupUnsavedGuard(Alpine) {
    Alpine.store('unsaved', { dirty: false });

    const forms = () => [...document.querySelectorAll('form[data-unsaved-guard]')];
    const baselines = new WeakMap();
    let allowLeave = false;

    /** Ringkasan isian form sebagai satu teks; dua keadaan form sama bila teksnya sama. */
    const signature = (form) => {
        const parts = [];

        for (const el of form.elements) {
            if (!el.name || el.disabled || IGNORED_NAMES.includes(el.name) || el.closest('[data-unsaved-ignore]')) {
                continue;
            }

            if (el.type === 'file') {
                const files = [...el.files].map((f) => `${f.name}:${f.size}:${f.lastModified}`).join('|');
                parts.push(`${el.name}:file:${files}`);
                continue;
            }

            if ((el.type === 'checkbox' || el.type === 'radio') && !el.checked) continue;

            if (el.tagName === 'SELECT' && el.multiple) {
                parts.push(`${el.name}=${[...el.selectedOptions].map((o) => o.value).join(',')}`);
                continue;
            }

            parts.push(`${el.name}=${el.value}`);
        }

        return parts.join('\u0001');
    };

    const isDirty = () =>
        forms().some((form) => {
            if (!baselines.has(form)) return false;

            return signature(form) !== baselines.get(form);
        });

    const takeBaselines = () => {
        forms().forEach((form) => {
            // Form yang dimuat ulang setelah galat validasi sudah berisi ketikan yang belum tersimpan.
            baselines.set(form, 'unsavedDirty' in form.dataset ? '\u0000belum-tersimpan' : signature(form));
        });

        refresh();
    };

    const refresh = () => {
        Alpine.store('unsaved').dirty = isDirty();
    };

    // Tunggu Alpine selesai membangun isian dinamis (daftar kartu, editor judul) sebelum mencatat keadaan awal.
    window.addEventListener('load', () => setTimeout(takeBaselines, 350));
    document.addEventListener('input', refresh, true);
    document.addEventListener('change', refresh, true);
    // Isian yang diubah lewat JavaScript (daftar kartu, editor judul) tidak memicu input/change.
    setInterval(refresh, POLL_MS);

    // Menyimpan form itu sendiri bukan "meninggalkan halaman". Pendengar ini dipasang setelah dialog konfirmasi
    // simpan, jadi hanya menerima pengiriman yang benar-benar dilanjutkan.
    document.addEventListener('submit', (event) => {
        if (!event.defaultPrevented && event.target instanceof HTMLFormElement && event.target.hasAttribute('data-unsaved-guard')) {
            allowLeave = true;
        }
    });

    // Keluar otomatis karena tidak aktif atau pengalihan sah lain.
    window.addEventListener('admin:allow-leave', () => {
        allowLeave = true;
    });

    window.addEventListener('beforeunload', (event) => {
        if (allowLeave || !isDirty()) return;

        event.preventDefault();
        event.returnValue = '';
    });

    document.addEventListener(
        'click',
        async (event) => {
            if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                return;
            }

            const link = event.target instanceof Element ? event.target.closest('a[href]') : null;

            if (!link || link.target === '_blank' || link.hasAttribute('download') || 'unsavedSkip' in link.dataset) return;

            const url = new URL(link.href, window.location.href);

            if (!['http:', 'https:'].includes(url.protocol)) return;

            // Tautan jangkar di halaman yang sama tidak meninggalkan halaman.
            if (url.origin === window.location.origin && url.pathname === window.location.pathname && url.search === window.location.search && url.hash) {
                return;
            }

            if (allowLeave || !isDirty()) return;

            event.preventDefault();
            event.stopPropagation();

            const choice = await Alpine.store('confirm').ask({
                title: 'Perubahan belum disimpan',
                message: 'Anda memiliki perubahan yang belum disimpan. Jika keluar sekarang, perubahan itu akan hilang.',
                label: 'Tetap di halaman',
                secondaryLabel: 'Keluar tanpa menyimpan',
                hideCancel: true,
            });

            if (choice === 'secondary') {
                allowLeave = true;
                window.location.href = link.href;
            }
        },
        true,
    );
}
