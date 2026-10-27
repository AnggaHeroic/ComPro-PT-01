import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.data('documentationLightbox', (images = []) => ({
    images,

    preview: false,
    previewIndex: 0,

    zoom: 1,
    minZoom: 1,
    maxZoom: 3,

    translateX: 0,
    translateY: 0,

    isDragging: false,

    dragStartX: 0,
    dragStartY: 0,

    startTranslateX: 0,
    startTranslateY: 0,

    pointers: {},

    pinchStartDistance: 0,
    pinchStartZoom: 1,

    openPreview(index, images = null) {
        // Opsional: pemanggil (mis. galeri admin) bisa mengirim daftar gambar
        // sesuai urutan yang sedang tampil. Halaman publik tidak mengirimnya.
        if (Array.isArray(images) && images.length) {
            this.images = images;
        }

        if (
            !this.images.length ||
            !Number.isInteger(index) ||
            index < 0 ||
            index >= this.images.length
        ) {
            return;
        }

        this.previewIndex = index;
        this.resetZoom();
        this.preview = true;

        document.body.classList.add('overflow-hidden');
    },

    closePreview() {
        this.preview = false;
        this.resetZoom();

        document.body.classList.remove('overflow-hidden');
    },

    nextPreview() {
        if (!this.images.length) return;

        if (this.previewIndex < this.images.length - 1) {
            this.previewIndex++;
        } else {
            this.previewIndex = 0;
        }

        this.resetZoom();
    },

    previousPreview() {
        if (!this.images.length) return;

        if (this.previewIndex > 0) {
            this.previewIndex--;
        } else {
            this.previewIndex = this.images.length - 1;
        }

        this.resetZoom();
    },

    resetZoom() {
        this.zoom = 1;
        this.translateX = 0;
        this.translateY = 0;

        this.isDragging = false;
        this.pointers = {};
    },

    zoomIn() {
        this.setZoom(this.zoom + 0.5);
    },

    zoomOut() {
        this.setZoom(this.zoom - 0.5);
    },

    toggleZoom() {
        if (this.zoom > 1) {
            this.resetZoom();
        } else {
            this.setZoom(2);
        }
    },

    setZoom(value) {
        this.zoom = Math.min(
            this.maxZoom,
            Math.max(this.minZoom, value)
        );

        if (this.zoom === 1) {
            this.translateX = 0;
            this.translateY = 0;
        } else {
            this.clampPosition();
        }
    },

    handleWheel(event) {
        if (!this.preview) return;

        const direction = event.deltaY < 0 ? 0.25 : -0.25;

        this.setZoom(this.zoom + direction);
    },

    handlePointerDown(event) {
        if (!this.preview) return;

        this.pointers[event.pointerId] = {
            x: event.clientX,
            y: event.clientY,
        };

        event.currentTarget.setPointerCapture(event.pointerId);

        const pointerIds = Object.keys(this.pointers);

        if (pointerIds.length === 2) {
            const first = this.pointers[pointerIds[0]];
            const second = this.pointers[pointerIds[1]];

            this.pinchStartDistance = Math.hypot(
                second.x - first.x,
                second.y - first.y
            );

            this.pinchStartZoom = this.zoom;
            this.isDragging = false;

            return;
        }

        if (this.zoom > 1) {
            this.isDragging = true;

            this.dragStartX = event.clientX;
            this.dragStartY = event.clientY;

            this.startTranslateX = this.translateX;
            this.startTranslateY = this.translateY;
        }
    },

    handlePointerMove(event) {
        if (
            !this.preview ||
            !this.pointers[event.pointerId]
        ) {
            return;
        }

        this.pointers[event.pointerId].x = event.clientX;
        this.pointers[event.pointerId].y = event.clientY;

        const pointerIds = Object.keys(this.pointers);

        if (pointerIds.length === 2) {
            const first = this.pointers[pointerIds[0]];
            const second = this.pointers[pointerIds[1]];

            const distance = Math.hypot(
                second.x - first.x,
                second.y - first.y
            );

            if (this.pinchStartDistance > 0) {
                const scale =
                    distance / this.pinchStartDistance;

                this.setZoom(
                    this.pinchStartZoom * scale
                );
            }

            return;
        }

        if (!this.isDragging || this.zoom <= 1) {
            return;
        }

        const deltaX =
            event.clientX - this.dragStartX;

        const deltaY =
            event.clientY - this.dragStartY;

        this.translateX =
            this.startTranslateX + deltaX;

        this.translateY =
            this.startTranslateY + deltaY;

        this.clampPosition();
    },

    handlePointerUp(event) {
        delete this.pointers[event.pointerId];

        const pointerIds = Object.keys(this.pointers);

        if (pointerIds.length < 2) {
            this.pinchStartDistance = 0;
        }

        if (pointerIds.length === 0) {
            this.isDragging = false;
        }
    },

    clampPosition() {
        if (this.zoom <= 1) {
            this.translateX = 0;
            this.translateY = 0;

            return;
        }

        const image = this.$refs.previewImage;

        if (!image) return;

        const maxX =
            (image.clientWidth * (this.zoom - 1)) / 2;

        const maxY =
            (image.clientHeight * (this.zoom - 1)) / 2;

        this.translateX = Math.min(
            maxX,
            Math.max(-maxX, this.translateX)
        );

        this.translateY = Math.min(
            maxY,
            Math.max(-maxY, this.translateY)
        );
    },
}));

/**
 * Carousel kartu layanan di beranda: geser otomatis ke samping, berhenti saat
 * kursor/fokus berada di dalamnya atau tab tidak aktif, dan tidak berputar
 * otomatis bila pengguna memilih "kurangi gerakan".
 */
Alpine.data('serviceCarousel', (total, interval = 6000) => ({
    total,
    current: 0,
    paused: false,
    timer: null,
    touchStartX: null,

    init() {
        const reduceMotion = window.matchMedia(
            '(prefers-reduced-motion: reduce)'
        ).matches;

        if (reduceMotion || this.total < 2) {
            return;
        }

        this.start();

        document.addEventListener('visibilitychange', () => {
            this.paused = document.hidden;
        });
    },

    start() {
        this.stop();

        this.timer = setInterval(() => {
            if (!this.paused) {
                this.next();
            }
        }, interval);
    },

    stop() {
        clearInterval(this.timer);
        this.timer = null;
    },

    restart() {
        // Hanya mulai ulang bila autoplay memang aktif.
        if (this.timer !== null) {
            this.start();
        }
    },

    next(manual = false) {
        this.current = (this.current + 1) % this.total;

        if (manual) this.restart();
    },

    prev(manual = false) {
        this.current = (this.current - 1 + this.total) % this.total;

        if (manual) this.restart();
    },

    go(index) {
        this.current = index;
        this.restart();
    },

    swipeStart(event) {
        this.touchStartX = event.changedTouches[0].clientX;
    },

    swipeEnd(event) {
        if (this.touchStartX === null) return;

        const delta = event.changedTouches[0].clientX - this.touchStartX;
        this.touchStartX = null;

        if (Math.abs(delta) < 50) return;

        delta < 0 ? this.next(true) : this.prev(true);
    },
}));

/**
 * Atur urutan baris tabel admin (kategori / aktivitas) dengan seret (pointer
 * events: mouse, sentuh, stylus) atau tombol panah. Perubahan hanya ditampung di layar; baru disimpan ke
 * server (setelah konfirmasi) saat admin menekan Selesai atau mematikan mode atur urutan.
 *
 * Setiap baris punya data-id dan data-group (kosong untuk kategori, id kategori
 * untuk aktivitas); baris hanya bisa dipindah di dalam grup yang sama.
 */
Alpine.data('reorderList', ({ url }) => ({
    state: 'idle', // idle | saving | saved | error
    message: '',
    dragRow: null,
    pointerId: null,
    lastY: 0,
    scrollTimer: null,
    snapshot: [],
    resetTimer: null,
    dirty: {}, // grup yang urutannya berubah tetapi belum disimpan

    init() {
        // Cegah kehilangan urutan yang belum disimpan saat halaman ditinggalkan.
        window.addEventListener('beforeunload', (event) => {
            if (Object.keys(this.dirty).length > 0) {
                event.preventDefault();
                event.returnValue = '';
            }
        });
    },

    rows(group = null) {
        const all = Array.from(this.$refs.body.querySelectorAll('tr[data-id]'));

        return group === null
            ? all
            : all.filter((row) => row.dataset.group === String(group));
    },

    ids(group) {
        return this.rows(group).map((row) => Number(row.dataset.id));
    },

    /**
     * Mulai menyeret. Mouse: dari mana saja di baris (kecuali tautan/tombol).
     * Sentuh dan stylus: hanya dari pegangan (data-drag-handle) agar halaman
     * tetap bisa digulir dengan jari di bagian lain tabel.
     */
    down(event) {
        const row = event.target.closest('tr[data-id]');

        if (!row) return;

        const handle = event.target.closest('[data-drag-handle]');

        if (event.pointerType === 'mouse') {
            if (event.button !== 0) return;
            if (!handle && event.target.closest('a, button, input, select, textarea, label')) return;
        } else if (!handle) {
            return;
        }

        event.preventDefault();

        this.dragRow = row;
        this.pointerId = event.pointerId;
        this.lastY = event.clientY;
        this.snapshot = this.rows();

        row.classList.add('opacity-60', 'shadow-lg', 'relative', 'z-10');
        this.$refs.body.setPointerCapture?.(event.pointerId);

        clearInterval(this.scrollTimer);
        this.scrollTimer = setInterval(() => this.autoScroll(), 16);
    },

    drag(event) {
        if (!this.dragRow || event.pointerId !== this.pointerId) return;

        event.preventDefault();
        this.lastY = event.clientY;

        const under = document.elementFromPoint(event.clientX, event.clientY);
        const target = under?.closest('tr[data-id]');

        if (
            !target ||
            target === this.dragRow ||
            target.dataset.group !== this.dragRow.dataset.group
        ) {
            return;
        }

        const rect = target.getBoundingClientRect();
        const after = event.clientY > rect.top + rect.height / 2;

        after ? target.after(this.dragRow) : target.before(this.dragRow);
    },

    drop(event) {
        if (!this.dragRow || (event && event.pointerId !== this.pointerId)) return;

        const row = this.dragRow;

        clearInterval(this.scrollTimer);
        this.scrollTimer = null;
        this.dragRow = null;

        row.classList.remove('opacity-60', 'shadow-lg', 'relative', 'z-10');
        this.$refs.body.releasePointerCapture?.(this.pointerId);

        const before = this.snapshot.map((item) => item.dataset.id).join();
        const after = this.rows().map((item) => item.dataset.id).join();

        if (before !== after) {
            this.stage(row.dataset.group);
        }
    },

    /**
     * Gulir halaman otomatis saat baris diseret mendekati tepi atas/bawah layar
     * (menyisakan ruang untuk header yang menempel di atas).
     */
    autoScroll() {
        if (!this.dragRow) return;

        const edge = 90;
        const top = 120;

        if (this.lastY < top) {
            window.scrollBy(0, -Math.ceil((top - this.lastY) / 6));
        } else if (this.lastY > window.innerHeight - edge) {
            window.scrollBy(0, Math.ceil((this.lastY - (window.innerHeight - edge)) / 4));
        }
    },

    move(event, delta) {
        const row = event.target.closest('tr[data-id]');

        if (!row) return;

        const group = row.dataset.group;
        const siblings = this.rows(group);
        const target = siblings[siblings.indexOf(row) + delta];

        if (!target) return;

        this.snapshot = this.rows();
        delta < 0 ? target.before(row) : target.after(row);
        row.scrollIntoView({ block: 'nearest', behavior: 'smooth' });

        this.stage(group);
    },

    /** Tandai urutan grup ini berubah (belum disimpan) dan perbarui nomor urut di layar. */
    stage(group) {
        clearTimeout(this.resetTimer);
        this.dirty[group] = true;
        this.renumber(group);
        this.state = 'dirty';
        this.message = '';
    },

    /**
     * Selesai: simpan urutan baru (setelah konfirmasi), lalu keluar dari mode atur urutan.
     * Tanpa perubahan langsung keluar. Dialog hanya berisi "Ya, simpan"; tutup dengan Esc,
     * klik di luar, atau tombol silang untuk tetap di mode ini.
     */
    async finish(url) {
        if (Object.keys(this.dirty).length === 0) {
            window.location.href = url;

            return;
        }

        const ok = await Alpine.store('confirm').ask({
            title: 'Simpan urutan baru?',
            message: 'Urutan baru akan diterapkan ke website.',
            label: 'Ya, simpan',
            hideCancel: true,
        });

        if (ok) await this.saveAndLeave(url);
    },

    /**
     * Batal (atau saklar atur urutan dimatikan): keluar dari mode atur urutan. Bila ada
     * perubahan yang belum disimpan, dialog hanya menawarkan "Ya, simpan" atau "Buang perubahan".
     */
    async cancel(url) {
        if (Object.keys(this.dirty).length === 0) {
            window.location.href = url;

            return;
        }

        const choice = await Alpine.store('confirm').ask({
            title: 'Keluar dari atur urutan?',
            message: 'Urutan baru belum disimpan. Simpan atau buang perubahan ini?',
            label: 'Ya, simpan',
            secondaryLabel: 'Buang perubahan',
            hideCancel: true,
        });

        if (choice === 'secondary') {
            this.dirty = {};
            window.location.href = url;
        } else if (choice === true) {
            await this.saveAndLeave(url);
        }
    },

    async saveAndLeave(url) {
        for (const group of Object.keys(this.dirty)) {
            if (!(await this.save(group))) return;
        }

        this.dirty = {};
        window.location.href = url;
    },

    restore() {
        this.snapshot.forEach((row) => this.$refs.body.appendChild(row));
    },

    renumber(group) {
        this.rows(group).forEach((row, index) => {
            row.querySelectorAll('[data-position]').forEach((cell) => {
                cell.textContent = index + 1;
            });
        });
    },

    async save(group) {
        clearTimeout(this.resetTimer);
        this.state = 'saving';
        this.message = '';

        const payload = { order: this.ids(group) };

        if (group !== '') {
            payload.category = Number(group);
        }

        try {
            const response = await fetch(url, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN':
                        document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                },
                body: JSON.stringify(payload),
            });

            if (!response.ok) {
                let message = 'Gagal menyimpan urutan.';

                try {
                    const data = await response.json();
                    message = data.errors
                        ? Object.values(data.errors).flat()[0]
                        : data.message ?? message;
                } catch (e) {
                    // abaikan: pakai pesan bawaan
                }

                throw new Error(message);
            }

            this.renumber(group);
            delete this.dirty[group];
            this.state = Object.keys(this.dirty).length > 0 ? 'dirty' : 'saved';

            return true;
        } catch (error) {
            this.state = 'error';
            this.message = error.message || 'Gagal menyimpan urutan.';

            return false;
        }
    },
}));

/**
 * Form konten halaman admin: tab per bagian, hanya bagian yang dipilih yang tampil
 * (semua bagian tetap ikut terkirim saat Simpan).
 */
Alpine.data('pageEditor', ({ active, sections }) => ({
    active,
    sections,

    select(key) {
        if (!this.sections.includes(key)) return;

        this.active = key;
        this.$nextTick(() => this.$refs.top?.scrollIntoView({ behavior: 'smooth', block: 'nearest' }));
    },

    /**
     * Kolom wajib yang kosong di bagian yang sedang disembunyikan tidak bisa difokuskan
     * browser; buka bagiannya lebih dulu lalu tampilkan pesannya.
     */
    onInvalid(event) {
        const section = event.target.closest('[data-section]')?.dataset.section;

        if (section && section !== this.active) {
            this.active = section;
            this.$nextTick(() => event.target.reportValidity());
        }
    },
}));

/**
 * Pratinjau satu bagian halaman publik di form konten. Isian form (yang belum disimpan)
 * dikirim ke server, yang merender halaman publik lalu menyisakan bagian itu saja;
 * hasilnya ditampilkan di iframe (lebar 1280 px) yang diperkecil agar muat.
 */
Alpine.data('sectionPreview', ({ url }) => ({
    open: true,
    loading: false,
    error: false,
    loadedOnce: false,
    dirty: false, // ada isian yang diubah dan belum disimpan
    scale: 0.5,
    height: 400,
    timer: null,

    init() {
        const form = this.$el.closest('form');
        const refresh = () => {
            // Hanya bagian yang sedang tampil yang diperbarui.
            if (this.$el.offsetParent !== null) this.schedule(900);
        };
        const edited = () => {
            this.dirty = true;
        };

        form.addEventListener('input', edited);
        form.addEventListener('change', edited);
        form.addEventListener('input', refresh);
        form.addEventListener('change', refresh);

        new ResizeObserver(() => this.rescale()).observe(this.$refs.box);
    },

    schedule(delay = 0) {
        clearTimeout(this.timer);
        this.timer = setTimeout(() => this.load(), delay);
    },

    rescale() {
        const width = this.$refs.box.clientWidth;

        if (width > 0) this.scale = width / 1280;
    },

    async load() {
        const data = new FormData(this.$el.closest('form'));

        // File tidak dikirim ke pratinjau; hanya teks.
        Array.from(data.entries()).forEach(([name, value]) => {
            if (value instanceof File) data.delete(name);
        });
        data.delete('_method');
        data.delete('bagian');

        this.loading = true;
        this.error = false;

        try {
            const response = await fetch(url, {
                method: 'POST',
                body: data,
                headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'text/html' },
            });

            if (!response.ok) throw new Error(String(response.status));

            this.$refs.frame.srcdoc = await response.text();
            this.loadedOnce = true;
        } catch (error) {
            this.error = true;
            this.loading = false;
        }
    },

    /** Setelah dokumen (dan gambarnya) selesai dimuat: sesuaikan tinggi iframe dengan isinya. */
    measure() {
        const frame = this.$refs.frame;
        const doc = frame.contentDocument;

        if (!frame.srcdoc || !doc?.documentElement) return;

        this.loading = false;

        // Dua kali ukur: bagian dengan tinggi minimum (mis. hero) menyesuaikan diri dengan viewport.
        // (tinggi body = tinggi isi; scrollHeight dokumen tidak bisa lebih kecil dari viewport iframe)
        const bodyHeight = () => Math.ceil(doc.body.getBoundingClientRect().height);

        frame.style.height = '900px';
        let height = bodyHeight();
        frame.style.height = height + 'px';
        height = bodyHeight();
        frame.style.height = height + 'px';

        this.height = height;
        this.rescale();
    },
}));

/** Masa tunggu setelah terlalu banyak salah login: tombol Masuk dinonaktifkan dengan hitung mundur, lalu peringatannya hilang. */
Alpine.data('loginLock', (seconds) => ({
    wait: Number(seconds) || 0,
    lock: Number(seconds) > 0, // halaman dibuka karena terkunci: peringatannya ikut hilang saat selesai

    init() {
        if (this.wait <= 0) return;

        const timer = setInterval(() => {
            this.wait = Math.max(0, this.wait - 1);
            if (this.wait === 0) clearInterval(timer);
        }, 1000);
    },
}));

/**
 * Penjaga ketidakaktifan admin. Aktivitas (gerak mouse, ketik, gulir, sentuh, klik) dicatat di
 * localStorage agar semua tab dashboard berbagi satu pewaktu. Selama admin aktif, server
 * di-ping berkala (agar sesi tidak dianggap diam walau form dibiarkan terbuka lama). Mendekati
 * batas, muncul peringatan dengan hitung mundur; aktivitas apa pun diabaikan sampai admin menekan
 * "Tetap masuk", dan peringatan tetap ada (dengan hitungan yang sama) saat pindah halaman atau memuat ulang;
 * melewati batas, admin dikeluarkan lewat server
 * (yang mencatatnya di riwayat aktivitas) dan diarahkan ke halaman login.
 */
Alpine.data('idleGuard', ({ timeout, warning, pingUrl, warningUrl, logoutUrl, loginUrl }) => ({
    remaining: null, // detik tersisa saat peringatan tampil; null = disembunyikan
    storageKey: 'admin-last-activity',
    warningKey: 'admin-idle-warning', // penanda bersama antar tab: peringatan sedang berjalan
    memory: Date.now(),
    lastPing: Date.now(),
    leaving: false,

    init() {
        // Memuat halaman dihitung sebagai aktivitas, kecuali peringatan sedang berjalan: berpindah halaman
        // atau memuat ulang tidak boleh mengulang hitungan mundur.
        const warningFrom = (timeout - warning) * 60 * 1000;

        if (Date.now() - this.lastActivity() < warningFrom) {
            this.touch(true);
        }

        ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart', 'click'].forEach((name) => {
            window.addEventListener(name, () => this.touch(), { passive: true, capture: true });
        });

        window.addEventListener('storage', (event) => {
            if (event.key === this.storageKey) this.tick();
        });

        this.tick();
        setInterval(() => this.tick(), 1000);
    },

    get clock() {
        const seconds = Math.max(0, this.remaining ?? 0);

        return String(Math.floor(seconds / 60)).padStart(2, '0') + ':' + String(seconds % 60).padStart(2, '0');
    },

    lastActivity() {
        try {
            return Number(window.localStorage.getItem(this.storageKey)) || this.memory;
        } catch {
            return this.memory;
        }
    },

    flagged() {
        try {
            return window.localStorage.getItem(this.warningKey) === '1';
        } catch {
            return false;
        }
    },

    setFlag(on) {
        try {
            on ? window.localStorage.setItem(this.warningKey, '1') : window.localStorage.removeItem(this.warningKey);
        } catch {
            // penyimpanan tidak tersedia
        }
    },

    /**
     * Catat aktivitas (maksimal sekali per detik); sesekali ping server selagi admin aktif.
     * Saat peringatan tampil, aktivitas biasa (gerak mouse, ketikan, gulir) tidak dihitung: sesi
     * hanya berlanjut bila admin menekan "Tetap masuk" (stay()).
     */
    touch(force = false) {
        if (this.remaining !== null && !force) return;

        const now = Date.now();

        if (!force && now - this.memory < 1000) return;

        this.memory = now;
        this.remaining = null;

        try {
            window.localStorage.setItem(this.storageKey, String(now));
        } catch {
            // penyimpanan tidak tersedia: pewaktu tetap jalan per tab
        }

        // Peringatan sebelumnya (mungkin di tab/halaman lain) selesai: server juga dilanjutkan.
        if (this.flagged()) {
            this.setFlag(false);
            this.ping();
        } else if (now - this.lastPing > 4 * 60 * 1000) {
            this.ping();
        }
    },

    tick() {
        if (this.leaving) return;

        const limit = timeout * 60 * 1000;
        const idle = Date.now() - this.lastActivity();

        if (idle >= limit) {
            this.expire();

            return;
        }

        const left = limit - idle;
        const inWarning = left <= warning * 60 * 1000;

        // Peringatan baru dimulai: beri tahu server agar jamnya selaras dan tidak disegarkan oleh pindah halaman.
        if (inWarning && this.remaining === null) {
            this.markWarning(Math.round(idle / 1000));
        }

        this.remaining = inWarning ? Math.ceil(left / 1000) : null;
    },

    headers() {
        return {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
        };
    },

    async markWarning(idleSeconds) {
        this.setFlag(true);

        try {
            await fetch(warningUrl, {
                method: 'POST',
                headers: { ...this.headers(), 'Content-Type': 'application/json' },
                body: JSON.stringify({ idle: idleSeconds }),
            });
        } catch {
            // jaringan terputus: server akan memakai waktu aktivitas terakhir yang diketahuinya
        }
    },

    async ping() {
        this.lastPing = Date.now();

        try {
            await fetch(pingUrl, { method: 'POST', headers: this.headers() });
        } catch {
            // jaringan terputus: coba lagi pada aktivitas berikutnya
        }
    },

    /** Tombol "Tetap masuk" pada peringatan. */
    stay() {
        this.setFlag(false);
        this.touch(true);
        this.ping();
    },

    async expire() {
        this.leaving = true;

        try {
            const response = await fetch(logoutUrl, { method: 'POST', headers: this.headers() });

            if (response.redirected) {
                window.location.href = response.url;

                return;
            }

            const data = await response.json().catch(() => ({}));

            // Server menilai admin masih aktif (mis. di tab lain): lanjutkan seperti biasa.
            if (data.active) {
                this.leaving = false;
                this.setFlag(false);
                this.touch(true);

                return;
            }

            this.setFlag(false);
            window.location.href = data.redirect ?? loginUrl;
        } catch {
            window.location.href = loginUrl;
        }
    },
}));

/**
 * Dialog konfirmasi admin. Dipakai otomatis untuk semua form non-GET di admin
 * (lihat listener di bawah) dan bisa dipanggil langsung:
 *   const ok = await Alpine.store('confirm').ask({ title, message, label, tone });
 */
Alpine.store('confirm', {
    open: false,
    title: '',
    message: '',
    label: 'Ya, lanjutkan',
    tone: 'primary', // primary | danger
    secondaryLabel: '', // bila diisi, muncul tombol tambahan yang menghasilkan 'secondary'
    hideCancel: false, // sembunyikan tombol Batal; dialog tetap bisa ditutup (Esc, klik di luar, tombol silang)
    step: '', // penanda langkah pada konfirmasi bertahap, mis. 'Langkah 1 dari 2'
    caution: '', // peringatan singkat yang ditampilkan dalam kotak "Perhatian"
    resolver: null,

    ask({ title, message, label, tone, secondaryLabel, hideCancel, step, caution } = {}) {
        this.resolver?.(false);
        this.title = title ?? 'Simpan perubahan?';
        this.message = message ?? 'Perubahan akan langsung diterapkan ke website.';
        this.label = label ?? 'Ya, lanjutkan';
        this.tone = tone ?? 'primary';
        this.secondaryLabel = secondaryLabel ?? '';
        this.hideCancel = hideCancel ?? false;
        this.step = step ?? '';
        this.caution = caution ?? '';
        this.open = true;

        return new Promise((resolve) => {
            this.resolver = resolve;
        });
    },

    answer(value) {
        this.open = false;
        this.resolver?.(value);
        this.resolver = null;
    },
});

/**
 * Setiap form non-GET di halaman admin (<body data-admin-confirm>) meminta
 * konfirmasi dulu. Atur teks lewat data-confirm-title / -message / -label,
 * atau lewati dengan data-no-confirm (mis. tombol keluar).
 */
document.addEventListener(
    'submit',
    async (event) => {
        const form = event.target;

        if (
            !document.body.hasAttribute('data-admin-confirm') ||
            !(form instanceof HTMLFormElement) ||
            'noConfirm' in form.dataset ||
            form.dataset.confirmed === '1'
        ) {
            return;
        }

        const method = (form.querySelector('input[name="_method"]')?.value || form.method).toUpperCase();

        if (method === 'GET') return;

        event.preventDefault();
        event.stopImmediatePropagation();

        const submitter = event.submitter;
        const isDelete = method === 'DELETE';

        const ok = await Alpine.store('confirm').ask({
            title: form.dataset.confirmTitle ?? (isDelete ? 'Hapus data?' : 'Simpan perubahan?'),
            message:
                form.dataset.confirmMessage ??
                (isDelete
                    ? 'Data akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.'
                    : 'Perubahan akan langsung diterapkan ke website.'),
            label: form.dataset.confirmLabel ?? (isDelete ? 'Ya, hapus' : 'Ya, simpan'),
            tone: isDelete ? 'danger' : 'primary',
            step: form.dataset.confirmSecondTitle ? 'Konfirmasi 1 dari 2' : '',
            caution: form.dataset.confirmCaution ?? '',
        });

        // Aksi besar (data-confirm-second-title) memerlukan konfirmasi kedua sebelum dikirim.
        const second = ok && form.dataset.confirmSecondTitle
            ? (await new Promise((resolve) => setTimeout(resolve, 200)),
              await Alpine.store('confirm').ask({
                  title: form.dataset.confirmSecondTitle,
                  message: form.dataset.confirmSecondMessage,
                  label: form.dataset.confirmSecondLabel ?? 'Ya, lanjutkan',
                  tone: form.dataset.confirmSecondTone ?? 'danger',
                  step: 'Konfirmasi 2 dari 2',
              }))
            : ok;

        if (!second) {
            // Unggahan otomatis (mis. ganti foto) dibatalkan: kosongkan pilihan file.
            form.querySelectorAll('input[type="file"][data-reset-on-cancel]').forEach((input) => {
                input.value = '';
            });

            return;
        }

        form.dataset.confirmed = '1';

        try {
            form.requestSubmit(submitter ?? undefined);
        } catch {
            form.submit();
        } finally {
            delete form.dataset.confirmed;
        }
    },
    true,
);

Alpine.start();