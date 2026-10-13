<section>
    <header>
        <h2 class="text-lg font-semibold text-gray-900">Informasi Profil</h2>
        <p class="mt-1 text-sm text-gray-500">Perbarui nama dan alamat email akun admin.</p>
    </header>

    <form method="post" action="{{ route('admin.profile.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" value="Nama" />
            <x-text-input id="name" name="name" type="text" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" name="email" type="email" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Simpan</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2500)"
                    class="text-sm font-medium text-green-600"
                >Tersimpan.</p>
            @endif
        </div>
    </form>
</section>
