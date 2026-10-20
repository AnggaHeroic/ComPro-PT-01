@props(['id', 'name', 'value' => '', 'max' => 200])

{{-- Editor judul sederhana: blok teks diberi warna merah dengan tombol; disimpan sebagai teks dengan penanda *kata*. --}}
<div
    x-data="{
        text: @js((string) $value),
        selection: '',
        escape(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; },
        get preview() {
            return this.escape(this.text).replace(/\*(.+?)\*/gs, '<span class=&quot;text-primary&quot;>$1</span>').replace(/\n/g, '<br>');
        },
        get balanced() { return (this.text.split('*').length - 1) % 2 === 0; },
        // Bungkus teks terpilih dengan *…*; bila sudah terbungkus, lepas tandanya.
        toggle() {
            const el = this.$refs.field;
            const a = el.selectionStart, b = el.selectionEnd;
            if (a === b) { this.hint = true; return; }
            this.hint = false;
            const t = this.text;
            let from = a, to = b, inner = t.slice(a, b);
            if (inner.startsWith('*') && inner.endsWith('*') && inner.length > 1) {
                this.text = t.slice(0, a) + inner.slice(1, -1) + t.slice(b);
                to = b - 2;
            } else if (t[a - 1] === '*' && t[b] === '*') {
                this.text = t.slice(0, a - 1) + inner + t.slice(b + 1);
                from = a - 1; to = b - 1;
            } else {
                this.text = t.slice(0, a) + '*' + inner + '*' + t.slice(b);
                to = b + 2;
            }
            this.$nextTick(() => { el.focus(); el.setSelectionRange(from, to); });
        },
        hint: false,
    }"
>
    <div class="mb-1.5 flex flex-wrap items-center gap-2">
        <button
            type="button"
            @click="toggle()"
            @mousedown.prevent
            class="inline-flex items-center gap-1.5 rounded border border-gray-300 bg-white px-2.5 py-1 text-xs font-semibold text-primary hover:bg-red-50"
        >
            <span class="h-3 w-3 rounded-sm bg-primary" aria-hidden="true"></span>
            Warnai merah
        </button>
        <span class="text-xs text-gray-500">Blok kata lalu klik tombol. Klik lagi pada kata berwarna untuk melepas.</span>
    </div>

    <textarea
        x-ref="field"
        id="{{ $id }}"
        name="{{ $name }}"
        x-model="text"
        rows="3"
        maxlength="{{ $max }}"
        required
        class="block w-full rounded border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 shadow-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
    ></textarea>

    <p x-show="hint" x-cloak class="mt-1.5 text-xs text-yellow-700">Blok dulu kata yang ingin diwarnai.</p>
    <p x-show="! balanced" x-cloak class="mt-1.5 text-xs text-primary">Tanda * harus berpasangan.</p>

    <div class="mt-2 rounded border border-dashed border-gray-300 bg-white px-3.5 py-3">
        <p class="mb-1 text-[11px] font-semibold uppercase tracking-wide text-gray-400">Pratinjau</p>
        <p class="font-display text-xl font-bold leading-tight text-secondary-dark" x-html="preview"></p>
    </div>

    <p class="mt-1.5 text-xs text-gray-500">Tekan Enter untuk pindah baris. Maksimal {{ $max }} karakter.</p>
</div>
