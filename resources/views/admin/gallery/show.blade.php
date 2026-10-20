@php
    $uploadErrors = collect($errors->getMessages())
        ->filter(fn ($messages, $key) => $key === 'images' || str_starts_with($key, 'images.'))
        ->flatten()
        ->unique();
    $publicUrl = $activity->is_published && $activity->category->is_published
        ? route('services.detail', [$activity->category->slug, $activity->slug])
        : null;
@endphp

<x-app-layout>
    <x-slot name="header">
        <h1 class="font-display font-bold text-gray-900" title="{{ $activity->name }}">{{ $activity->name }}</h1>
        <p class="text-gray-500">
            {{ $activity->category->name }}
            &middot; {{ $images->count() }} dari {{ $maxPerActivity }} foto
            @if ($activity->kbli_code) &middot; KBLI <span class="font-mono">{{ $activity->kbli_code }}</span> @endif
        </p>
    </x-slot>

    <x-slot name="actions">
        <x-secondary-button size="sm" :href="route('admin.activities.edit', $activity)">Edit aktivitas</x-secondary-button>
        @if ($publicUrl)
            <x-secondary-button size="sm" :href="$publicUrl" target="_blank" rel="noopener">Lihat di website &nearr;</x-secondary-button>
        @endif
        <x-admin.back :href="route('admin.gallery.index', ['category' => $activity->service_category_id])" />
    </x-slot>

    {{-- Unggah foto --}}
    <x-admin.card title="Tambah foto" :description="'Pilih hingga '.$maxPerUpload.' foto sekaligus. JPG, PNG, atau WebP, maksimal '.number_format(config('images.max_upload_kb') / 1024, 0).' MB per foto; otomatis dikonversi ke WebP dan dikompres.'" class="mb-8">
        <form
            method="POST"
            action="{{ route('admin.gallery.store', $activity) }}"
            enctype="multipart/form-data"
            data-confirm-title="Unggah foto?"
            data-confirm-message="Foto yang dipilih akan ditambahkan ke galeri dan tampil di website."
            data-confirm-label="Ya, unggah"
            x-data="{ count: 0 }"
            class="flex flex-wrap items-start gap-3"
        >
            @csrf
            <div class="min-w-0 flex-1">
                <input
                    type="file"
                    name="images[]"
                    multiple
                    required
                    accept="image/jpeg,image/png,image/webp"
                    @change="count = $event.target.files.length"
                    class="block w-full text-sm text-gray-600 file:mr-3 file:rounded file:border file:border-gray-300 file:bg-white file:px-3 file:py-2 file:text-sm file:font-medium file:text-gray-700 hover:file:bg-gray-50"
                >
                @if ($uploadErrors->isNotEmpty())
                    <ul class="mt-2 space-y-1 text-sm text-primary">
                        @foreach ($uploadErrors as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <x-primary-button x-bind:disabled="count === 0">
                Unggah <span x-show="count > 0" x-text="'(' + count + ')'"></span>
            </x-primary-button>
        </form>
    </x-admin.card>

    {{-- Daftar foto --}}
    @if ($images->isEmpty())
        <div class="rounded border border-dashed border-gray-300 px-6 py-14 text-center">
            <p class="font-medium text-gray-700">Galeri masih kosong</p>
            <p class="mt-1 text-sm text-gray-500">Halaman aktivitas di website tetap tampil normal tanpa bagian galeri. Unggah foto di atas untuk menambahkannya.</p>
        </div>
    @else
        <div
            x-data="{
                ids: @js($images->pluck('id')->all()),
                original: @js($images->pluck('id')->all()),
                urls: @js($images->pluck('image_url', 'id')),
                dragging: null,
                get dirty() { return this.ids.join() !== this.original.join(); },
                move(id, delta) {
                    const from = this.ids.indexOf(id), to = from + delta;
                    if (to < 0 || to >= this.ids.length) return;
                    this.ids.splice(to, 0, this.ids.splice(from, 1)[0]);
                },
                openPhoto(id) {
                    this.$dispatch('documentation-open', {
                        index: this.ids.indexOf(id),
                        images: this.ids.map((i) => this.urls[i]),
                    });
                },
                dropOn(target) {
                    if (this.dragging === null || this.dragging === target) return;
                    this.ids.splice(this.ids.indexOf(target), 0, this.ids.splice(this.ids.indexOf(this.dragging), 1)[0]);
                    this.dragging = null;
                },
            }"
        >
            <form method="POST" action="{{ route('admin.gallery.reorder', $activity) }}" data-confirm-title="Simpan urutan foto?" data-confirm-message="Urutan baru langsung dipakai di website." data-confirm-label="Ya, simpan" class="mb-4 flex flex-wrap items-center justify-between gap-3">
                @csrf
                @method('PUT')

                <p class="text-sm text-gray-500">Klik foto untuk pratinjau. Seret foto atau gunakan tombol panah untuk mengurutkan; urutan ini dipakai di website.</p>

                <template x-for="id in ids" :key="id">
                    <input type="hidden" name="order[]" :value="id">
                </template>

                <div class="flex items-center gap-3">
                    <span x-show="dirty" x-cloak class="text-xs text-amber-700">Urutan belum disimpan</span>
                    <x-primary-button x-bind:disabled="! dirty">Simpan urutan</x-primary-button>
                </div>
            </form>

            @error('order')
                <p class="mb-3 text-sm text-primary">{{ $message }}</p>
            @enderror

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($images as $image)
                    @php($bag = $errors->getBag('image'.$image->id))
                    <article
                        :style="{ order: ids.indexOf({{ $image->id }}) }"
                        @dragover.prevent
                        @drop.prevent="dropOn({{ $image->id }})"
                        :class="dragging === {{ $image->id }} ? 'opacity-50' : ''"
                        class="flex flex-col overflow-hidden rounded border border-gray-200 bg-white shadow-sm"
                    >
                        <div
                            draggable="true"
                            role="button"
                            tabindex="0"
                            aria-label="Lihat foto {{ $loop->iteration }}"
                            @keydown.enter.prevent="openPhoto({{ $image->id }})"
                            @keydown.space.prevent="openPhoto({{ $image->id }})"
                            @dragstart="dragging = {{ $image->id }}"
                            @dragend="dragging = null"
                            class="group relative aspect-[4/3] cursor-zoom-in bg-gray-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-primary"
                            title="Klik untuk pratinjau, seret untuk mengurutkan"
                        >
                            <img src="{{ $image->image_url }}" alt="{{ $image->caption ?: 'Foto '.$loop->iteration }}" class="h-full w-full object-cover" loading="lazy" draggable="false" @click="openPhoto({{ $image->id }})">

                            {{-- Ikon zoom saat hover (sama seperti dokumentasi di halaman publik) --}}
                            <span class="pointer-events-none absolute inset-0 flex items-center justify-center bg-black/0 transition-colors duration-300 group-hover:bg-black/25">
                                <span class="flex h-10 w-10 scale-90 items-center justify-center rounded-full bg-white/90 text-secondary-dark opacity-0 transition-all duration-300 group-hover:scale-100 group-hover:opacity-100">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5M11 8v6M8 11h6"/></svg>
                                </span>
                            </span>

                            <span class="absolute left-2 top-2 rounded-sm bg-black/60 px-2 py-0.5 text-xs font-semibold text-white" x-text="ids.indexOf({{ $image->id }}) + 1"></span>

                            <div class="absolute right-2 top-2 flex gap-1">
                                <button type="button" @click="move({{ $image->id }}, -1)" :disabled="ids.indexOf({{ $image->id }}) === 0" aria-label="Geser ke depan" class="flex h-7 w-7 items-center justify-center rounded-sm bg-white/90 text-gray-700 hover:bg-white disabled:opacity-40">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5m7 7-7-7 7-7"/></svg>
                                </button>
                                <button type="button" @click="move({{ $image->id }}, 1)" :disabled="ids.indexOf({{ $image->id }}) === ids.length - 1" aria-label="Geser ke belakang" class="flex h-7 w-7 items-center justify-center rounded-sm bg-white/90 text-gray-700 hover:bg-white disabled:opacity-40">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14m-7-7 7 7-7 7"/></svg>
                                </button>
                            </div>
                        </div>

                        <div class="p-3" x-data="{ dirty: {{ $bag->any() ? 'true' : 'false' }}, fileName: '' }">
                            <form
                                id="update-{{ $image->id }}"
                                method="POST"
                                data-confirm-title="Terapkan perubahan foto?"
                                data-confirm-message="Perubahan pada foto ini langsung diterapkan ke website."
                                data-confirm-label="Ya, simpan"
                                action="{{ route('admin.gallery.update', [$activity, $image]) }}"
                                enctype="multipart/form-data"
                            >
                                @csrf
                                @method('PATCH')

                                <textarea
                                    name="caption"
                                    rows="2"
                                    maxlength="255"
                                    placeholder="Deskripsi singkat foto..."
                                    aria-label="Deskripsi singkat foto {{ $loop->iteration }}"
                                    @input="dirty = true"
                                    @keydown.ctrl.enter.prevent="$el.form.requestSubmit()"
                                    class="block w-full resize-none rounded border border-gray-300 bg-white px-2.5 py-2 text-[13px] leading-snug text-gray-900 placeholder:text-gray-400 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                                >{{ $bag->any() ? old('caption', $image->caption) : $image->caption }}</textarea>

                                <p x-show="fileName" x-cloak class="mt-1.5 truncate text-xs text-amber-700">Foto pengganti: <span x-text="fileName" class="font-medium"></span> — tekan Simpan untuk menerapkan.</p>

                                <div class="mt-2 flex items-center gap-1.5">
                                    {{-- Ganti foto: pilih file, lalu tekan Simpan (dengan konfirmasi) --}}
                                    <label class="inline-flex h-8 cursor-pointer items-center gap-1.5 rounded border border-gray-300 bg-white px-2.5 text-xs font-semibold text-gray-600 transition focus-within:ring-2 focus-within:ring-primary hover:border-primary hover:text-primary">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 16V4m0 0L7 9m5-5 5 5"/><path d="M4 16v3a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-3"/></svg>
                                        Ganti
                                        <span class="sr-only">foto {{ $loop->iteration }}</span>
                                        <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="sr-only" @change="dirty = true; fileName = $el.files[0]?.name ?? ''">
                                    </label>

                                    {{-- Hapus: mengirim form terpisah di bawah (atribut form) --}}
                                    <button type="submit" form="delete-{{ $image->id }}" aria-label="Hapus foto {{ $loop->iteration }}" class="inline-flex h-8 items-center gap-1.5 rounded border border-gray-300 bg-white px-2.5 text-xs font-semibold text-gray-600 transition hover:border-primary hover:bg-primary-light hover:text-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7h16"/><path d="M10 11v6M14 11v6"/><path d="M6 7l1 12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1l1-12"/><path d="M9 7V4h6v3"/></svg>
                                        Hapus
                                    </button>

                                    {{-- Simpan deskripsi: muncul hanya setelah deskripsi diubah --}}
                                    <button type="submit" x-show="dirty" x-cloak title="Simpan deskripsi" aria-label="Simpan deskripsi" class="ml-auto inline-flex h-8 items-center gap-1.5 rounded bg-primary px-3 text-xs font-semibold text-white transition hover:bg-primary-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-1">
                                        Simpan
                                    </button>
                                </div>
                            </form>

                            <form id="delete-{{ $image->id }}" method="POST" action="{{ route('admin.gallery.destroy', [$activity, $image]) }}" data-confirm-title="Hapus foto?" data-confirm-message="Foto ini akan dihapus permanen dari galeri. Tindakan ini tidak dapat dibatalkan." data-confirm-label="Ya, hapus" class="hidden">
                                @csrf
                                @method('DELETE')
                            </form>

                            <x-input-error :messages="array_merge($bag->get('caption'), $bag->get('image'))" class="mt-2 text-xs" />
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- Pratinjau foto (komponen yang sama dengan halaman publik) --}}
            <x-img-lightbox :images="$images->pluck('image_url')->all()" />
        </div>
    @endif
</x-app-layout>
