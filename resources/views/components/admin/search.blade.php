@props(['name' => 'q', 'value' => '', 'placeholder' => 'Cari...'])

<div class="relative min-w-0 flex-1 sm:w-72 sm:flex-none">
    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
    <x-text-input :name="$name" type="search" :value="$value" :placeholder="$placeholder" style="padding-left: 2.25rem" />
</div>
