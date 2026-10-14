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
        <nav class="mb-2 text-xs text-gray-500" aria-label="Breadcrumb">
            <a href="{{ route('admin.gallery.index') }}" class="hover:text-primary">Galeri</a>
            <span class="mx-1">/</span>
            <a href="{{ route('admin.gallery.index', ['category' => $activity->service_category_id]) }}" class="hover:text-primary">{{ $activity->category->name }}</a>
        </nav>

        <div class="flex flex-wrap items-end justify-between gap-4">
            <div class="min-w-0">
                <h1 class="font-display text-2xl font-bold text-gray-900">{{ $activity->name }}</h1>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $images->count() }} dari {{ $maxPerActivity }} foto
                    @if ($activity->kbli_code) &middot; KBLI <span class="font-mono">{{ $activity->kbli_code }}</span> @endif
                </p>
            </div>

            <div class="flex items-center gap-4 text-sm">
                <a href="{{ route('admin.activities.edit', $activity) }}" class="font-medium text-gray-600 hover:text-primary">Edit aktivitas</a>
                @if ($publicUrl)
                    <a href="{{ $publicUrl }}" target="_blank" rel="noopener" class="font-medium text-gray-600 hover:text-primary">Lihat di website &nearr;</a>
                @else
                    <span class="text-xs text-gray-400">Belum terbit di website</span>
                @endif
            </div>
        </div>
    </x-slot>

    {{-- Unggah foto --}}
    <x-admin.card title="Tambah foto" :description="'Pilih hingga '.$maxPerUpload.' foto sekaligus. JPG, PNG, atau WebP, maksimal '.number_format(config('images.max_upload_kb') / 1024, 0).' MB per foto; otomatis dikonversi ke WebP dan dikompres.'" class="mb-8">
        <form
            method="POST"
            action="{{ route('admin.gallery.store', $activity) }}"
            enctype="multipart/form-data"
            x-data="{ count: 0 }"
            class="mt-5 flex flex-wrap items-start gap-3"
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
                    class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-md file:border file:border-gray-300 file:bg-white file:px-3 file:py-2 file:text-sm file:font-medium file:text-gray-700 hover:file:bg-gray-50"
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
        <div class="rounded-md border border-dashed border-gray-300 px-6 py-14 text-center">
            <p class="font-medium text-gray-700">Galeri masih kosong</p>
            <p class="mt-1 text-sm text-gray-500">Halaman aktivitas di website tetap tampil normal tanpa bagian galeri. Unggah foto di atas untuk menambahkannya.</p>
        </div>
    @else
        <div
            x-data="{
                ids: @js($images->pluck('id')->all()),
                original: @js($images->pluck('id')->all()),
                dragging: null,
                get dirty() { return this.ids.join() !== this.original.join(); },
                move(id, delta) {
                    const from = this.ids.indexOf(id), to = from + delta;
                    if (to < 0 || to >= this.ids.length) return;
                    this.ids.splice(to, 0, this.ids.splice(from, 1)[0]);
                },
                dropOn(target) {
                    if (this.dragging === null || this.dragging === target) return;
                    this.ids.splice(this.ids.indexOf(target), 0, this.ids.splice(this.ids.indexOf(this.dragging), 1)[0]);
                    this.dragging = null;
                },
            }"
        >
            <form method="POST" action="{{ route('admin.gallery.reorder', $activity) }}" class="mb-4 flex flex-wrap items-center justify-between gap-3">
                @csrf
                @method('PUT')

                <p class="text-sm text-gray-500">Seret foto atau gunakan tombol panah untuk mengurutkan. Urutan ini dipakai di website.</p>

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
                        class="flex flex-col overflow-hidden rounded-md border border-gray-200 bg-white"
                    >
                        <div
                            draggable="true"
                            @dragstart="dragging = {{ $image->id }}"
                            @dragend="dragging = null"
                            class="group relative aspect-[4/3] cursor-grab bg-gray-100 active:cursor-grabbing"
                            title="Seret untuk mengurutkan"
                        >
                            <img src="{{ $image->image_url }}" alt="{{ $image->caption ?: 'Foto '.$loop->iteration }}" class="h-full w-full object-cover" loading="lazy" draggable="false">

                            <span class="absolute left-2 top-2 rounded-sm bg-black/60 px-2 py-0.5 text-xs font-semibold text-white" x-text="ids.indexOf({{ $image->id }}) + 1"></span>

                            <div class="absolute right-2 top-2 flex gap-1">
                                <button type="button" @click="move({{ $image->id }}, -1)" :disabled="ids.indexOf({{ $image->id }}) === 0" aria-label="Geser ke depan" class="flex h-7 w-7 items-center justify-center rounded-sm bg-white/90 text-gray-700 hover:bg-white disabled:opacity-40">
                                    <x-icons.arrow-left class="h-3.5 w-3.5" />
                                </button>
                                <button type="button" @click="move({{ $image->id }}, 1)" :disabled="ids.indexOf({{ $image->id }}) === ids.length - 1" aria-label="Geser ke belakang" class="flex h-7 w-7 items-center justify-center rounded-sm bg-white/90 text-gray-700 hover:bg-white disabled:opacity-40">
                                    <x-icons.arrow-right class="h-3.5 w-3.5" />
                                </button>
                            </div>
                        </div>

                        <div class="flex flex-1 flex-col gap-3 p-4">
                            <form method="POST" action="{{ route('admin.gallery.update', [$activity, $image]) }}" enctype="multipart/form-data" class="space-y-3">
                                @csrf
                                @method('PATCH')

                                <div>
                                    <x-input-label :for="'caption-'.$image->id" value="Caption (opsional)" />
                                    <x-text-input :id="'caption-'.$image->id" name="caption" type="text" maxlength="255" :value="$bag->any() ? old('caption', $image->caption) : $image->caption" placeholder="Keterangan foto" />
                                    <x-input-error :messages="$bag->get('caption')" class="mt-1.5" />
                                </div>

                                <div>
                                    <x-input-label :for="'replace-'.$image->id" value="Ganti foto (opsional)" />
                                    <input
                                        id="replace-{{ $image->id }}"
                                        type="file"
                                        name="image"
                                        accept="image/jpeg,image/png,image/webp"
                                        class="block w-full text-xs text-gray-600 file:mr-2 file:rounded-md file:border file:border-gray-300 file:bg-white file:px-2.5 file:py-1.5 file:text-xs file:font-medium file:text-gray-700 hover:file:bg-gray-50"
                                    >
                                    <x-input-error :messages="$bag->get('image')" class="mt-1.5" />
                                </div>

                                <x-primary-button class="w-full !py-2 text-xs">Simpan perubahan</x-primary-button>
                            </form>

                            <form method="POST" action="{{ route('admin.gallery.destroy', [$activity, $image]) }}" onsubmit="return confirm('Hapus foto ini dari galeri? Tindakan ini tidak dapat dibatalkan.')" class="mt-auto text-right">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-medium text-gray-500 hover:text-primary">Hapus foto</button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    @endif
</x-app-layout>
