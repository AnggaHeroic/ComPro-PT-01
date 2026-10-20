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
    $noPreview = ['seo']; // bagian yang tidak tampil di halaman
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
        class="[&_.shadow-sm]:shadow-none [&_section]:rounded-sm [&_header]:rounded-t-sm [&_input]:rounded-sm [&_textarea]:rounded-sm [&_button]:rounded-sm"
        x-data="pageEditor({
            active: @js($initialSection),
            sections: @js($sectionKeys),
        })"
        @invalid.capture="onInvalid($event)"
    >
        @csrf
        @method('PUT')
        <input type="hidden" name="bagian" :value="active">

        <div class="max-w-3xl">
            <div class="min-w-0" x-ref="top">

                {{-- Pilih bagian --}}
                <div class="rounded-sm border border-gray-300 bg-white p-4 sm:p-5">
                    <h2 class="text-sm font-semibold text-gray-900">Bagian halaman</h2>
                    <p class="mt-0.5 text-xs leading-5 text-gray-500">Pilih bagian yang ingin diubah. Perubahan di semua bagian disimpan sekaligus saat Anda menekan Simpan.</p>

                    <div class="mt-4 flex flex-wrap gap-2" role="tablist" aria-label="Bagian halaman">
                        @foreach ($definition['sections'] as $sectionKey => $section)
                            <button
                                type="button"
                                role="tab"
                                id="tab-{{ $sectionKey }}"
                                @click="select('{{ $sectionKey }}')"
                                :aria-selected="active === '{{ $sectionKey }}'"
                                :class="active === '{{ $sectionKey }}' ? 'border-primary bg-primary text-white' : 'border-gray-300 bg-white text-gray-700 hover:border-gray-400 hover:bg-gray-50'"
                                class="inline-flex items-center gap-2 rounded-sm border px-3 py-1.5 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-1"
                            >
                                <span
                                    :class="active === '{{ $sectionKey }}' ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500'"
                                    class="flex h-5 min-w-5 items-center justify-center rounded-sm text-[11px] font-bold"
                                    aria-hidden="true"
                                >{{ $loop->iteration }}</span>
                                {{ $section['label'] }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Form per bagian: hanya bagian aktif yang tampil (semua tetap ikut terkirim saat Simpan) --}}
                <div class="mt-4">
                    @foreach ($definition['sections'] as $sectionKey => $section)
                        <div x-show="active === '{{ $sectionKey }}'" x-cloak data-section="{{ $sectionKey }}" role="tabpanel" aria-labelledby="tab-{{ $sectionKey }}">
                            <x-admin.card :title="$section['label']" :description="$section['description'] ?? null">
                                @if (in_array($sectionKey, $noPreview, true))
                                    <p class="mb-5 rounded bg-gray-50 px-3 py-2 text-xs text-gray-500">Bagian ini tidak tampil di halaman; hanya dibaca mesin pencari.</p>
                                @endif

                                @unless (in_array($sectionKey, $noPreview, true))
                                    <x-admin.section-preview :url="route('admin.pages.preview', ['page' => $page, 'section' => $sectionKey])" :section="$sectionKey" />
                                @endunless

                                <div class="mb-5 flex items-center gap-3">
                                    <h3 class="text-sm font-semibold text-gray-900">Konten bagian ini</h3>
                                    <span class="h-px flex-1 bg-gray-200" aria-hidden="true"></span>
                                </div>

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
                            <div class="mt-4 flex items-center justify-between gap-3 text-sm">
                                @if (! $loop->first)
                                    <button type="button" @click="select('{{ $sectionKeys[$loop->index - 1] }}')" class="inline-flex items-center gap-1 font-medium text-gray-600 transition hover:text-primary focus:outline-none focus-visible:text-primary" title="{{ $definition['sections'][$sectionKeys[$loop->index - 1]]['label'] }}">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m15 5-7 7 7 7"/></svg>
                                        Sebelumnya
                                    </button>
                                @else
                                    <span></span>
                                @endif
                                @if (! $loop->last)
                                    <button type="button" @click="select('{{ $sectionKeys[$loop->index + 1] }}')" class="inline-flex items-center gap-1 font-medium text-gray-600 transition hover:text-primary focus:outline-none focus-visible:text-primary" title="{{ $definition['sections'][$sectionKeys[$loop->index + 1]]['label'] }}">
                                        Berikutnya
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/></svg>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

        <x-admin.form-actions :cancel="route('admin.pages.index')" />
    </form>

    {{-- Setelan default halaman --}}
    <section class="mt-8 rounded-sm border border-gray-300 bg-white">
        <header class="border-b border-gray-300 bg-gray-50 px-4 py-3 sm:px-5">
            <h2 class="text-sm font-semibold text-gray-900">Setelan default</h2>
            <p class="mt-0.5 text-xs leading-5 text-gray-500">
                @if ($content->hasCustomDefaults())
                    Setelan default halaman ini ditetapkan oleh admin pada {{ $content->defaultsUpdatedAt()->locale('id')->translatedFormat('d M Y, H:i') }}.
                @else
                    Halaman ini memakai setelan default bawaan sistem.
                @endif
                Tombol "Kembali ke setelan default" mengembalikan seluruh konten halaman ke setelan tersebut.
            </p>
        </header>

        <div class="flex flex-wrap items-start gap-3 p-4 sm:p-5">
            <form
                method="POST"
                action="{{ route('admin.pages.default.set', $page) }}"
                data-confirm-title="Jadikan konten ini setelan default?"
                data-confirm-message="Konten halaman {{ $definition['label'] }} yang sudah tersimpan akan menjadi setelan default baru, menggantikan default sebelumnya. Perubahan di form yang belum disimpan tidak ikut."
                data-confirm-label="Lanjutkan"
                data-confirm-second-title="Yakin menetapkan default baru?"
                data-confirm-second-message="Ini mencakup seluruh konten halaman {{ $definition['label'] }} di website publik (teks, judul, daftar, dan foto). Setelan default sebelumnya diganti. Tampilan website saat ini tidak berubah."
                data-confirm-second-label="Ya, tetapkan default baru"
                data-confirm-second-tone="primary"
            >
                @csrf
                <x-secondary-button type="submit">Jadikan konten tersimpan sebagai default</x-secondary-button>
            </form>

            <form
                method="POST"
                action="{{ route('admin.pages.reset', $page) }}"
                data-confirm-title="Kembali ke setelan default?"
                data-confirm-message="Seluruh konten halaman {{ $definition['label'] }} akan dikembalikan ke setelan default{{ $content->hasCustomDefaults() ? ' yang ditetapkan admin' : ' bawaan sistem' }}. Perubahan yang pernah disimpan akan hilang."
                data-confirm-label="Lanjutkan"
                data-confirm-second-title="Yakin mengembalikan seluruh halaman?"
                data-confirm-second-message="Seluruh teks, judul, daftar, dan foto halaman {{ $definition['label'] }} di website publik akan langsung berubah ke setelan default dan tidak bisa dibatalkan."
                data-confirm-second-label="Ya, kembalikan ke default"
            >
                @csrf
                @method('DELETE')
                <x-secondary-button type="submit" class="border-red-300! text-red-700! hover:border-red-400! hover:bg-red-50!">Kembali ke setelan default</x-secondary-button>
            </form>

            @if ($content->hasCustomDefaults())
                <form
                    method="POST"
                    action="{{ route('admin.pages.default.clear', $page) }}"
                    data-confirm-title="Kembali ke default semula?"
                    data-confirm-message="Setelan default yang ditetapkan admin untuk halaman {{ $definition['label'] }} dihapus dan acuannya kembali ke default bawaan sistem. Tampilan website saat ini tidak berubah."
                    data-confirm-label="Lanjutkan"
                    data-confirm-second-title="Yakin menghapus default kustom?"
                    data-confirm-second-message="Ini mencakup seluruh konten halaman {{ $definition['label'] }}: setelah ini, &quot;Kembali ke setelan default&quot; akan memakai teks dan foto bawaan sistem, bukan lagi default yang Anda tetapkan."
                    data-confirm-second-label="Ya, hapus default kustom"
                >
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-1 py-2.5 text-sm font-medium text-gray-600 underline-offset-2 hover:text-primary hover:underline">Pakai default bawaan sistem</button>
                </form>
            @endif
        </div>
    </section>
</x-app-layout>
