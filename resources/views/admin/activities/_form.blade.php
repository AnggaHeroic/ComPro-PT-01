@csrf

<div class="max-w-3xl space-y-6">
    <x-admin.card title="Informasi aktivitas" description="Kategori induk, nama, dan kode KBLI aktivitas usaha.">
        <div class="space-y-5">
            <div>
                <x-input-label for="service_category_id" value="Kategori" />
                <x-select id="service_category_id" name="service_category_id" required class="w-full">
                    <option value="">— Pilih kategori —</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((int) old('service_category_id', $activity->service_category_id) === $category->id)>{{ $category->name }}</option>
                    @endforeach
                </x-select>
                <x-input-error :messages="$errors->get('service_category_id')" class="mt-2" />
            </div>

            <div class="grid gap-5 sm:grid-cols-3">
                <div class="sm:col-span-2">
                    <x-input-label for="name" value="Nama aktivitas" />
                    <x-text-input id="name" name="name" type="text" :value="old('name', $activity->name)" required maxlength="255" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="kbli_code" value="Kode KBLI" />
                    <x-text-input id="kbli_code" name="kbli_code" type="text" :value="old('kbli_code', $activity->kbli_code)" maxlength="10" placeholder="opsional" class="font-mono" />
                    <x-input-error :messages="$errors->get('kbli_code')" class="mt-2" />
                </div>
            </div>

            <x-admin.url-field
                prefix="/layanan-jasa/…/"
                :value="$activity->slug"
                :exists="$activity->exists"
                :published="$activity->is_published"
            />
        </div>
    </x-admin.card>

    <x-admin.card title="Deskripsi" description="Teks singkat tampil di kartu kategori, teks detail tampil di halaman aktivitas.">
        <div class="space-y-5">
            <div>
                <x-input-label for="short_description" value="Deskripsi singkat (kartu di halaman kategori)" />
                <x-textarea id="short_description" name="short_description" rows="2" maxlength="500">{{ old('short_description', $activity->short_description) }}</x-textarea>
                <x-input-error :messages="$errors->get('short_description')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="description" value="Deskripsi detail (halaman detail aktivitas)" />
                <x-textarea id="description" name="description" rows="6" maxlength="10000">{{ old('description', $activity->description) }}</x-textarea>
                <x-input-error :messages="$errors->get('description')" class="mt-2" />
            </div>
        </div>
    </x-admin.card>

    <x-admin.card title="Gambar utama" description="Foto dokumentasi tambahan dikelola di menu Galeri.">
        <x-admin.image-field label="Gambar utama" :current="$activity->image_url" />
    </x-admin.card>

    <x-admin.card title="Penayangan" description="Atur status dan urutan di dalam kategori.">
        <div class="space-y-5">
            <x-admin.status-field :selected="old('status', $activity->exists ? ($activity->is_published ? 'published' : 'draft') : 'published')" />

            <div class="sm:max-w-xs">
                <x-input-label for="sort_order" value="Urutan tampil" />
                <x-text-input id="sort_order" name="sort_order" type="number" min="0" max="65535" :value="old('sort_order', $activity->sort_order)" />
                <x-input-error :messages="$errors->get('sort_order')" class="mt-2" />
                <p class="mt-1.5 text-xs text-gray-500">Urutan berlaku di dalam kategorinya.</p>
            </div>
        </div>
    </x-admin.card>
</div>

<x-admin.form-actions :cancel="route('admin.activities.index', array_filter(['category' => $activity->service_category_id]))" />
