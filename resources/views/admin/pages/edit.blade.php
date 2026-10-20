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

    <form method="POST" action="{{ route('admin.pages.update', $page) }}">
        @csrf
        @method('PUT')

        <div class="max-w-3xl space-y-6">
            @foreach ($definition['sections'] as $section)
                <x-admin.card :title="$section['label']" :description="$section['description'] ?? null">
                    <div class="space-y-5">
                        @foreach ($section['fields'] as $key => $field)
                            <div>
                                <x-input-label :for="'content_'.$key" :value="$field['label']" />

                                @if (($field['type'] ?? 'text') === 'textarea')
                                    <x-textarea :id="'content_'.$key" :name="'content['.$key.']'" rows="4" :maxlength="$field['max'] ?? 200" required>{{ old('content.'.$key, $content($key)) }}</x-textarea>
                                @else
                                    <x-text-input :id="'content_'.$key" :name="'content['.$key.']'" type="text" :value="old('content.'.$key, $content($key))" :maxlength="$field['max'] ?? 200" required />
                                @endif

                                @if (! empty($field['hint']))
                                    <p class="mt-1.5 text-xs text-gray-500">{{ $field['hint'] }}</p>
                                @endif
                                <x-input-error :messages="$errors->get('content.'.$key)" class="mt-2" />
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
        onsubmit="return confirm('Kembalikan semua teks halaman ini ke bawaan? Perubahan yang pernah disimpan akan hilang.')"
    >
        @csrf
        @method('DELETE')
        <button type="submit" class="text-sm font-medium text-red-700 underline-offset-2 hover:underline">Kembalikan semua teks ke bawaan</button>
    </form>
</x-app-layout>
