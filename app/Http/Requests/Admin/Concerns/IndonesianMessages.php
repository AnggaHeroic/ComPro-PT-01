<?php

namespace App\Http\Requests\Admin\Concerns;

trait IndonesianMessages
{
    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'max.string' => ':attribute maksimal :max karakter.',
            'max.file' => 'Ukuran :attribute maksimal :max KB.',
            'min.numeric' => ':attribute minimal :min.',
            'max.numeric' => ':attribute maksimal :max.',
            'integer' => ':attribute harus berupa angka bulat.',
            'unique' => ':attribute sudah dipakai.',
            'regex' => 'Format :attribute tidak valid.',
            'exists' => ':attribute yang dipilih tidak valid.',
            'image' => ':attribute harus berupa gambar.',
            'mimes' => ':attribute harus berformat JPG, PNG, atau WebP.',
            'dimensions' => 'Dimensi :attribute terlalu besar (maksimal :max_width x :max_height piksel).',
            'array' => ':attribute tidak valid.',
            'min.array' => 'Pilih minimal :min file.',
            'max.array' => 'Maksimal :max file sekali unggah.',
            'distinct' => ':attribute berisi duplikat.',
            'uploaded' => ':attribute gagal diunggah. Periksa ukuran file.',
        ];
    }
}
