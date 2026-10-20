@php
    $publicRoute = ['home' => 'home', 'about' => 'about', 'contact' => 'contact', 'services' => 'services'][$page] ?? null;
    $sectionKeys = array_keys($definition['sections']);

    // Bagian yang memuat error validasi dibuka otomatis; selain itu bagian dari ?bagian= atau yang pertama.
    $errorSection = null;
    foreach ($definition['sections'] as $sectionKey => $section) {
        foreach (array_keys($section['fields']) as $fieldKey) {
            foreach ($errors->keys() as $errorKey) {
                if ($errorKey === 'content.'.$fieldKey || $errorKey === 'image_'.$fieldKey || str_starts_with($errorKey, 'content.'.$fieldKey.'.')) {
                    $errorSection ??= $sectionKey;
                }
            }
        }
    }
    $requested = request('bagian');
    $initialSection = $errorSection ?? (in_array($requested, $sectionKeys, true) ? $requested : $sectionKeys[0]);
    $noPreview = ['seo'];
@endphp

<x-app-layout>
    <x-slot name="header">
        <h1 class="font-display text-2xl font-bold text-gray-900">Konten {{ $definition['label'] }}</h1>
        <p class="mt-1 text-sm text-gray-500">{{ $definition['description'] }}</p>
    </x-slot>

    <x-slot name="actions">
        <x-admin.back :href="route('admin.pages.index')" />
        @if ($publicRoute)
            <x-secondary-button :href="route($publicRoute)" target="_blank" rel="noopener" size="sm" class="max-sm:hidden">Lihat halaman</x-secondary-button>
        @endif
    </x-slot>

    <form
        method="POST"
        action="{{ route('admin.pages.update', $page) }}"
        enctype="multipart/form-data"
        x-data="pageEditor({
            active: @js($initialSection),
            sections: @js($sectionKeys),
            noPreview: @js($noPreview),
            external: @js(['identity' => ['label' => 'Identitas perusahaan (logo & footer)', 'url' => route('admin.pages.edit', ['page' => 'contact', 'bagian' => 'identity']), 'here' => $page === 'contact']]),
            previewUrl: @js($publicRoute ? route($publicRoute, ['cms-preview' => 1]) : null),
        })"
        @invalid.capture="onInvalid($event)"
    >
        @csrf
        @method('PUT')
        <input type="hidden" name="bagian" :value="active">

        <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_24rem]">

            {{-- ============ KOLOM FORM ============ --}}
            <div class="min-w-0" x-ref="top">

                {{-- Pilih bagian --}}
                <div class="rounded border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-semibold text-gray-900">Pilih bagian yang ingin diubah</p>
                        <button type="button" x-show="previewUrl && ! previewOpen" x-cloak @click="openPreview()" class="inline-flex items-center gap-1.5 text-xs font-semibold text-primary hover:underline xl:hidden">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                            Tampilkan pratinjau
                        </button>
                    </div>
                    <p class="mt-0.5 text-xs text-gray-500">Pilih tab di bawah{{ $publicRoute ? ', atau klik bagiannya langsung pada pratinjau halaman' : '' }}. Perubahan di semua bagian disimpan sekaligus saat Anda menekan Simpan.</p>

                    <div class="mt-3">
                        <div class="flex flex-wrap gap-2" role="tablist" aria-label="Bagian halaman">
                            @foreach ($definition['sections'] as $sectionKey => $section)
                                <button
                                    type="button"
                                    role="tab"
                                    id="tab-{{ $sectionKey }}"
                                    @click="select('{{ $sectionKey }}')"
                                    :aria-selected="active === '{{ $sectionKey }}'"
                                    :class="active === '{{ $sectionKey }}' ? 'border-primary bg-primary text-white shadow-sm' : 'border-gray-300 bg-white text-gray-700 hover:border-primary hover:text-primary'"
                                    class="inline-flex items-center gap-2 rounded border px-3 py-1.5 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-1"
                                >
                                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-black/10 text-[11px] font-bold" aria-hidden="true">{{ $loop->iteration }}</span>
                                    {{ $section['label'] }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Pemberitahuan bila yang diklik di pratinjau diatur di halaman lain --}}
                <div x-show="notice" x-cloak x-transition class="mt-4 flex items-start justify-between gap-3 rounded border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900" role="status">
                    <p>
                        <span x-text="notice?.label"></span> diatur
                        <template x-if="notice && ! notice.here"><span>di menu <a :href="notice.url" class="font-semibold underline">Kontak Kami</a>.</span></template>
                        <template x-if="notice && notice.here"><span>pada bagian di atas.</span></template>
                    </p>
                    <button type="button" @click="notice = null" aria-label="Tutup" class="shrink-0 text-amber-700 hover:text-amber-900">&times;</button>
                </div>

                {{-- Form per bagian: hanya bagian aktif yang tampil (semua tetap ikut terkirim saat Simpan) --}}
                <div class="mt-4">
                    @foreach ($definition['sections'] as $sectionKey => $section)
                        <div x-show="active === '{{ $sectionKey }}'" x-cloak data-section="{{ $sectionKey }}" role="tabpanel" aria-labelledby="tab-{{ $sectionKey }}">
                            <x-admin.card :title="$section['label']" :description="$section['description'] ?? null">
                                @if (in_array($sectionKey, $noPreview, true))
                                    <p class="mb-5 rounded bg-gray-50 px-3 py-2 text-xs text-gray-500">Bagian ini tidak tampil di halaman; hanya dibaca mesin pencari, sehingga tidak ada di pratinjau.</p>
                                @endif

                                <div class="space-y-6">
                                    @foreach ($section['fields'] as $key => $field)
                                        @php
                                            $type = $field['type'] ?? 'text';
                                            $isText = in_array($type, ['text', 'textarea', 'title'], true);
                                            $max = $field['max'] ?? 200;
                                            $optional = $field['optional'] ?? false;
                                            $value = old('content.'.$key, $isText ? $content($key) : null);
                                            $messages = collect($errors->getMessages())
                                                ->filter(fn ($m, $errorKey) => $errorKey === 'content.'.$key || $errorKey === 'image_'.$key || str_starts_with($errorKey, 'content.'.$key.'.'))
                                                ->flatten()->unique()->all();
                                        @endphp

                                        @if ($isText)
                                            <div x-data="{ n: {{ mb_strlen((string) $value) }} }" @input="n = $event.target.value.length">
                                                {{-- Label + penghitung karakter --}}
                                                <div class="mb-1.5 flex items-baseline justify-between gap-3">
                                                    <label for="content_{{ $key }}" class="block text-sm font-medium text-gray-700">
                                                        {{ $field['label'] }}
                                                        @unless ($optional)<span class="text-primary" title="Wajib diisi" aria-hidden="true">*</span>@endunless
                                                    </label>
                                                    <span class="text-xs tabular-nums text-gray-400" :class="n > {{ (int) ($max * 0.9) }} && 'font-semibold text-amber-600'" x-text="n + '/{{ $max }}'"></span>
                                                </div>

                                                @if ($type === 'title')
                                                    <x-admin.title-field :id="'content_'.$key" :name="'content['.$key.']'" :value="$value" :max="$max" />
                                                @elseif ($type === 'textarea')
                                                    <x-textarea :id="'content_'.$key" :name="'content['.$key.']'" rows="4" :maxlength="$max" :required="! $optional">{{ $value }}</x-textarea>
                                                @else
                                                    <x-text-input :id="'content_'.$key" :name="'content['.$key.']'" type="text" :value="$value" :maxlength="$max" :required="! $optional" />
                                                @endif

                                                @if (! empty($field['hint']))
                                                    <p class="mt-1.5 flex gap-1.5 text-xs leading-5 text-gray-500">
                                                        <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8h.01M11 12h1v5h1m8-5a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                                        <span>{{ $field['hint'] }}</span>
                                                    </p>
                                                @endif
                                                <x-input-error :messages="$messages" class="mt-2" />
                                            </div>
                                        @elseif ($type === 'image')
                                            <div>
                                                @php($custom = $content->customImagePath($key))
                                                <x-admin.image-field
                                                    :name="'image_'.$key"
                                                    :remove-name="'remove_image_'.$key"
                                                    :label="$field['label']"
                                                    :current="$content->image($key)"
                                                    :removable="$custom !== null"
                                                    :fallback="$custom !== null ? $content->defaultImage($key) : null"
                                                    remove-label="Kembalikan gambar bawaan"
                                                    :hint="$field['hint'] ?? null"
                                                />
                                            </div>
                                        @else
                                            <div>
                                                <p class="mb-1.5 text-sm font-medium text-gray-700">{{ $field['label'] }}</p>

                                                @if ($type === 'list')
                                                    <x-admin.list-field
                                                        :name="'content['.$key.'][]'"
                                                        :items="old('content.'.$key, $content->list($key))"
                                                        :item-label="$field['item_label'] ?? 'Butir'"
                                                        :max="$max"
                                                        :max-items="$field['max_items'] ?? 10"
                                                        :min-items="$field['min_items'] ?? 1"
                                                    />
                                                @elseif ($type === 'cards')
                                                    <x-admin.card-list-field
                                                        :name="'content['.$key.']'"
                                                        :items="old('content.'.$key, $content->cards($key))"
                                                        :item-label="$field['item_label'] ?? 'Butir'"
                                                        :max-title="$field['max_title'] ?? 60"
                                                        :max-text="$field['max_text'] ?? 300"
                                                        :max-items="$field['max_items'] ?? 10"
                                                        :min-items="$field['min_items'] ?? 1"
                                                    />
                                                @endif

                                                @if (! empty($field['hint']))
                                                    <p class="mt-1.5 text-xs leading-5 text-gray-500">{{ $field['hint'] }}</p>
                                                @endif
                                                <x-input-error :messages="$messages" class="mt-2" />
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </x-admin.card>

                            {{-- Pindah antar bagian --}}
                            <div class="mt-4 flex items-center justify-between text-sm">
                                @if (! $loop->first)
                                    <button type="button" @click="select('{{ $sectionKeys[$loop->index - 1] }}')" class="inline-flex items-center gap-1.5 font-medium text-gray-600 hover:text-primary">&larr; {{ $definition['sections'][$sectionKeys[$loop->index - 1]]['label'] }}</button>
                                @else
                                    <span></span>
                                @endif
                                @if (! $loop->last)
                                    <button type="button" @click="select('{{ $sectionKeys[$loop->index + 1] }}')" class="inline-flex items-center gap-1.5 font-medium text-gray-600 hover:text-primary">{{ $definition['sections'][$sectionKeys[$loop->index + 1]]['label'] }} &rarr;</button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ============ PRATINJAU HALAMAN ============ --}}
            @if ($publicRoute)
                <aside class="xl:sticky xl:top-20" x-show="previewOpen" x-cloak aria-label="Pratinjau halaman">
                    <div class="overflow-hidden rounded border border-gray-200 bg-white shadow-sm">
                        <div class="flex items-center justify-between gap-2 border-b border-gray-200 bg-gray-50 px-3 py-2.5">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900">Pratinjau halaman</p>
                                <p class="truncate text-xs text-gray-500">Klik bagian untuk mengeditnya</p>
                            </div>
                            <div class="flex shrink-0 items-center gap-1">
                                <button type="button" @click="reloadPreview()" title="Muat ulang pratinjau" aria-label="Muat ulang pratinjau" class="rounded p-1.5 text-gray-500 hover:bg-gray-200 hover:text-gray-800">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12a8 8 0 1 1-2.3-5.7M20 4v5h-5"/></svg>
                                </button>
                                <button type="button" x-show="! wide" @click="previewOpen = false" title="Sembunyikan pratinjau" aria-label="Sembunyikan pratinjau" class="rounded p-1.5 text-gray-500 hover:bg-gray-200 hover:text-gray-800 xl:hidden">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18"/></svg>
                                </button>
                            </div>
                        </div>

                        <div x-ref="pane" class="relative h-[70vh] overflow-hidden bg-slate-100 xl:h-[calc(100vh-13rem)]">
                            <template x-if="previewOpen">
                                <iframe
                                    x-ref="frame"
                                    :src="previewUrl"
                                    title="Pratinjau halaman"
                                    class="absolute left-0 top-0 origin-top-left border-0 bg-white"
                                    :style="`width: 1280px; height: ${frameHeight}px; transform: scale(${scale});`"
                                    @load="syncActive(false)"
                                ></iframe>
                            </template>
                        </div>

                        <p class="border-t border-gray-200 bg-gray-50 px-3 py-2 text-[11px] leading-4 text-gray-500">Pratinjau menampilkan versi yang sudah tersimpan. Setelah menekan Simpan, pratinjau dimuat ulang otomatis.</p>
                    </div>
                </aside>
            @endif
        </div>

        <x-admin.form-actions :cancel="route('admin.pages.index')" />
    </form>

    <form
        method="POST"
        action="{{ route('admin.pages.reset', $page) }}"
        class="mt-6"
        data-confirm-title="Kembali ke setelan default?" data-confirm-message="Semua teks dan foto halaman ini akan dikembalikan ke setelan default. Perubahan yang pernah disimpan akan hilang." data-confirm-label="Ya, kembalikan"
    >
        @csrf
        @method('DELETE')
        <button type="submit" class="text-sm font-medium text-red-700 underline-offset-2 hover:underline">Kembali ke setelan default</button>
    </form>
</x-app-layout>
