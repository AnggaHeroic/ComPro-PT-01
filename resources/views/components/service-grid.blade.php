@props([
    'categories',
    'offset' => 0,
    'gridClass' => 'grid grid-cols-2 gap-2 sm:gap-3 lg:grid-cols-3',
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
