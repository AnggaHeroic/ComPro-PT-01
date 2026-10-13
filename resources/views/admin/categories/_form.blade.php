@csrf

<div class="space-y-5">
    <div>
        <x-input-label for="name" value="Nama kategori" />
        <x-text-input id="name" name="name" type="text" :value="old('name', $category->name)" required maxlength="120" autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="slug" value="Slug (bagian dari URL)" />
        <x-text-input id="slug" name="slug" type="text" :value="old('slug', $category->slug)" maxlength="120" placeholder="dibuat otomatis dari nama bila dikosongkan" />
        <x-input-error :messages="$errors->get('slug')" class="mt-2" />
        @if ($category->exists)
            <p class="mt-1.5 text-xs text-amber-700">Mengubah slug akan mengubah URL publik kategori ini dan semua aktivitas di dalamnya. Tautan lama akan menghasilkan halaman 404.</p>
        @else
            <p class="mt-1.5 text-xs text-gray-500">Hanya huruf kecil, angka, dan tanda hubung. Contoh: <span class="font-mono">technology-digital</span>.</p>
        @endif
    </div>

    <div>
        <x-input-label for="description" value="Deskripsi" />
        <x-textarea id="description" name="description" rows="4" maxlength="2000">{{ old('description', $category->description) }}</x-textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    <x-admin.image-field label="Gambar kategori" :current="$category->image_url" />

    <div class="space-y-5 rounded-md border border-gray-200 bg-gray-50/60 p-5">
        <div>
            <h3 class="text-sm font-semibold text-gray-900">Ikon kartu di beranda</h3>
            <p class="mt-0.5 text-xs text-gray-500">Pilih ikon dari daftar, atau unggah ikon sendiri. Ikon unggahan diutamakan bila ada.</p>
        </div>

        <x-admin.icon-picker
            name="icon"
            :selected="old('icon', $category->exists ? $category->resolved_icon : \App\Support\CategoryIcons::DEFAULT)"
            :options="\App\Support\CategoryIcons::options()"
        />

        <x-admin.image-field
            name="icon_image"
            remove-name="remove_icon_image"
            label="Ikon kustom (opsional)"
            remove-label="Hapus ikon kustom"
            :current="$category->icon_image_url"
            :contain="true"
            hint="PNG atau WebP transparan, disarankan persegi (mis. 256×256 px). SVG tidak diterima demi keamanan."
        />
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <x-input-label for="sort_order" value="Urutan tampil" />
            <x-text-input id="sort_order" name="sort_order" type="number" min="0" max="65535" :value="old('sort_order', $category->sort_order)" />
            <x-input-error :messages="$errors->get('sort_order')" class="mt-2" />
            <p class="mt-1.5 text-xs text-gray-500">Angka kecil tampil lebih dulu.</p>
        </div>

        <div>
            <x-input-label for="is_published" value="Status" />
            <input type="hidden" name="is_published" value="0">
            <label class="mt-2 inline-flex items-center gap-2 text-sm text-gray-700">
                <input id="is_published" type="checkbox" name="is_published" value="1" @checked(old('is_published', $category->is_published)) class="h-4 w-4 rounded border-gray-300 accent-primary">
                Tampilkan di website
            </label>
            <x-input-error :messages="$errors->get('is_published')" class="mt-2" />
        </div>
    </div>
</div>

<div class="mt-8 flex items-center gap-3 border-t border-gray-200 pt-6">
    <x-primary-button>Simpan</x-primary-button>
    <a href="{{ route('admin.categories.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Batal</a>
</div>
