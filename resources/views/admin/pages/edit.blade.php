<x-app-layout>
    <x-slot name="header">
        <h1 class="font-display text-2xl font-bold text-gray-900">Konten {{ $definition['label'] }}</h1>
        <p class="mt-1 text-sm text-gray-500">{{ $definition['description'] }}</p>
    </x-slot>

    <x-slot name="actions">
        <x-admin.back :href="route('admin.pages.index')" />
        @if ($page === 'home')
            <x-secondary-button :href="route('home')" target="_blank" rel="noopener" size="sm">Lihat halaman</x-secondary-button>
        @endif
    </x-slot>

    <form method="POST" action="{{ route('admin.pages.update', $page) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="max-w-3xl space-y-6">
            @foreach ($definition['sections'] as $section)
                <x-admin.card :title="$section['label']" :description="$section['description'] ?? null">
                    <div class="space-y-5">
                        @foreach ($section['fields'] as $key => $field)
                            <div>
                                @if (($field['type'] ?? 'text') !== 'image')
                                    <x-input-label :for="'content_'.$key" :value="$field['label']" />
                                @endif

                                @if (($field['type'] ?? 'text') === 'image')
                                    @php($custom = $content->customImagePath($key))
                                    <x-admin.image-field
                                        :name="'image_'.$key"
                                        :remove-name="'remove_image_'.$key"
                                        :label="$field['label']"
                                        :current="$content->image($key)"
                                        :removable="$custom !== null"
                                        :fallback="$custom !== null ? $field['default'] : null"
                                        remove-label="Kembalikan gambar bawaan"
                                        :hint="$field['hint'] ?? null"
                                    />
                                @elseif (($field['type'] ?? 'text') === 'list')
                                    <x-admin.list-field
                                        :name="'content['.$key.'][]'"
                                        :items="old('content.'.$key, $content->list($key))"
                                        :item-label="$field['item_label'] ?? 'Butir'"
                                        :max="$field['max'] ?? 300"
                                        :max-items="$field['max_items'] ?? 10"
                                        :min-items="$field['min_items'] ?? 1"
                                    />
                                @elseif (($field['type'] ?? 'text') === 'title')
                                    <x-admin.title-field
                                        :id="'content_'.$key"
                                        :name="'content['.$key.']'"
                                        :value="old('content.'.$key, $content($key))"
                                        :max="$field['max'] ?? 200"
                                    />
                                @elseif (($field['type'] ?? 'text') === 'cards')
                                    <x-admin.card-list-field
                                        :name="'content['.$key.']'"
                                        :items="old('content.'.$key, $content->cards($key))"
                                        :item-label="$field['item_label'] ?? 'Butir'"
                                        :max-title="$field['max_title'] ?? 60"
                                        :max-text="$field['max_text'] ?? 300"
                                        :max-items="$field['max_items'] ?? 10"
                                        :min-items="$field['min_items'] ?? 1"
                                    />
                                @elseif (($field['type'] ?? 'text') === 'textarea')
                                    <x-textarea :id="'content_'.$key" :name="'content['.$key.']'" rows="4" :maxlength="$field['max'] ?? 200" :required="! ($field['optional'] ?? false)">{{ old('content.'.$key, $content($key)) }}</x-textarea>
                                @else
                                    <x-text-input :id="'content_'.$key" :name="'content['.$key.']'" type="text" :value="old('content.'.$key, $content($key))" :maxlength="$field['max'] ?? 200" required />
                                @endif

                                @if (! empty($field['hint']) && ($field['type'] ?? 'text') !== 'image')
                                    <p class="mt-1.5 text-xs text-gray-500">{{ $field['hint'] }}</p>
                                @endif
                                <x-input-error :messages="collect($errors->getMessages())->filter(fn ($messages, $errorKey) => $errorKey === 'content.'.$key || str_starts_with($errorKey, 'content.'.$key.'.'))->flatten()->unique()->all()" class="mt-2" />
                            </div>
                        @endforeach
                    </div>
                </x-admin.card>
            @endforeach
        </div>

        <x-admin.form-actions :cancel="route('admin.pages.index')" />
    </form>

    <form
        method="POST"
        action="{{ route('admin.pages.reset', $page) }}"
        class="mt-6 max-w-3xl"
        data-confirm-title="Kembalikan ke bawaan?" data-confirm-message="Semua teks dan foto halaman ini akan dikembalikan ke bawaan. Perubahan yang pernah disimpan akan hilang." data-confirm-label="Ya, kembalikan"
    >
        @csrf
        @method('DELETE')
        <button type="submit" class="text-sm font-medium text-red-700 underline-offset-2 hover:underline">Kembalikan semua teks ke bawaan</button>
    </form>
</x-app-layout>
