@props([
    'categories',
    'offset' => 0,
    'gridClass' => 'grid gap-3 sm:grid-cols-2 lg:grid-cols-3',
])

<div class="{{ $gridClass }}">

    @foreach ($categories as $serviceCategory)
        <x-service-card
            :number="$offset + $loop->iteration"
            :icon="$serviceCategory->resolved_icon"
            :icon-url="$serviceCategory->icon_image_url"
            :title="$serviceCategory->name"
            :description="\Illuminate\Support\Str::limit($serviceCategory->description, 140)"
            :href="route('services.category', $serviceCategory->slug) . '#' . $serviceCategory->slug"
        />
    @endforeach
</div>
