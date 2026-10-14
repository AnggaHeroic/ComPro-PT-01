@csrf

<div class="max-w-3xl space-y-6">
    <x-admin.card title="Informasi kategori" description="Nama dan deskripsi tampil di halaman Layanan & Jasa.">
        <div class="space-y-5">
            <div>
                <x-input-label for="name" value="Nama kategori" />
                <x-text-input id="name" name="name" type="text" :value="old('name', $category->name)" required maxlength="120" autofocus />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="description" value="Deskripsi" />
                <x-textarea id="description" name="description" rows="4" maxlength="2000">{{ old('description', $category->description) }}</x-textarea>
                <x-input-error :messages="$errors->get('description')" class="mt-2" />
            </div>
        </div>
    </x-admin.card>

    <x-admin.card title="Gambar" description="Gambar sampul kategori. Otomatis dikonversi ke WebP.">
        <x-admin.image-field label="Gambar kategori" :current="$category->image_url" />
    </x-admin.card>

    <x-admin.card title="Ikon kartu di beranda" description="Pilih ikon dari daftar, atau unggah ikon sendiri. Ikon unggahan diutamakan bila ada.">
        <div class="space-y-6">
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
    </x-admin.card>

    <x-admin.card title="Penayangan" description="Atur status dan urutan tampil di website.">
        <div class="space-y-5">
            <x-admin.status-field
                :selected="old('status', $category->exists ? ($category->is_published ? 'published' : 'draft') : 'published')"
                :exists="$category->exists"
                :url="$category->slug ? route('services.category', $category->slug, false) : null"
                noun="kategori"
            />

            <div class="sm:max-w-xs">
                <x-input-label for="sort_order" value="Urutan tampil" />
                <x-text-input id="sort_order" name="sort_order" type="number" min="0" max="65535" :value="old('sort_order', $category->sort_order)" />
                <x-input-error :messages="$errors->get('sort_order')" class="mt-2" />
                <p class="mt-1.5 text-xs text-gray-500">Angka kecil tampil lebih dulu.</p>
            </div>
        </div>
    </x-admin.card>
</div>

<x-admin.form-actions :cancel="route('admin.categories.index')" />
