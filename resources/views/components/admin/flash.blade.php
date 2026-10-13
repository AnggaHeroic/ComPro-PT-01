@if (session('success'))
    <div
        x-data="{ show: true }"
        x-show="show"
        x-transition
        role="status"
        class="mb-6 flex items-start justify-between gap-4 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800"
    >
        <span>{{ session('success') }}</span>
        <button type="button" @click="show = false" class="text-green-700 hover:text-green-900" aria-label="Tutup">&times;</button>
    </div>
@endif

@if (session('error'))
    <div
        x-data="{ show: true }"
        x-show="show"
        x-transition
        role="alert"
        class="mb-6 flex items-start justify-between gap-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"
    >
        <span>{{ session('error') }}</span>
        <button type="button" @click="show = false" class="text-red-700 hover:text-red-900" aria-label="Tutup">&times;</button>
    </div>
@endif
