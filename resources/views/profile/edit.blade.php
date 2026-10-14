<x-app-layout>
    <x-slot name="header">
        <x-admin.back :href="route('admin.dashboard')" label="Kembali ke Dashboard" />
        <h1 class="font-display text-2xl font-bold text-gray-900">Profil</h1>
        <p class="mt-1 text-sm text-gray-500">Kelola informasi dan keamanan akun admin.</p>
    </x-slot>

    <div class="max-w-2xl space-y-6">
        <x-admin.card>
            @include('profile.partials.update-profile-information-form')
        </x-admin.card>

        <x-admin.card>
            @include('profile.partials.update-password-form')
        </x-admin.card>
    </div>
</x-app-layout>
