<x-guest-layout>
    <div class="mb-8">
        <h1 class="font-display text-2xl font-bold text-gray-900">Masuk</h1>
        <p class="mt-1.5 text-sm text-gray-500">Gunakan akun admin untuk mengakses panel pengelolaan.</p>
    </div>

    <x-auth-session-status class="mb-5" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5" x-data="loginLock({{ (int) session('login_wait', 0) }})">
        @csrf

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="nama@email.com" />
            {{-- Peringatan masa tunggu hilang sendiri saat hitung mundur 10 detik selesai --}}
            <div x-show="! lock || wait > 0">
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>
        </div>

        <div x-data="{ show: false }">
            <x-input-label for="password" value="Password" />
            <div class="relative">
                <x-text-input id="password" x-bind:type="show ? 'text' : 'password'" type="password" name="password" required autocomplete="current-password" placeholder="Masukkan password" class="pe-16" />
                <button type="button" @click="show = ! show" class="absolute inset-y-0 inset-e-0 px-3.5 text-xs font-medium text-gray-500 hover:text-primary focus:outline-none" x-text="show ? 'Sembunyikan' : 'Tampilkan'">Tampilkan</button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <x-primary-button class="w-full" x-bind:disabled="wait > 0">
            <span x-show="wait === 0">Masuk</span>
            <span x-show="wait > 0" x-cloak>Coba lagi dalam <span x-text="wait"></span> detik</span>
        </x-primary-button>
    </form>
</x-guest-layout>
