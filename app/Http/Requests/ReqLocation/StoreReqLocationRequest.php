<?php

namespace App\Http\Requests\ReqLocation;

use Illuminate\Foundation\Http\FormRequest;

class StoreReqLocationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'student_name'   => 'required|string|max:255',
            'nim'            => 'required|string|max:10',
            'name_location'  => 'required|string|max:255',
            'description'    => 'required|string',
            'latitude'       => 'required|numeric',
            'longitude'      => 'required|numeric',
            'id_category'    => 'required|exists:category,id_category',
            'image'          => 'required|image|max:2048'
        ];
    }

    public function messages(): array
    {
        return [
            'student_name.required'  => 'Nama mahasiswa wajib diisi.',
            'nim.required'           => 'NIM wajib diisi.',
            'name_location.required' => 'Nama lokasi wajib diisi.',
            'description.required'   => 'Deskripsi wajib diisi.',
            'latitude.required'      => 'Latitude wajib diisi.',
            'longitude.required'     => 'Longitude wajib diisi.',
            'id_category.required'   => 'Kategori wajib dipilih.',
            'image.required'         => 'Gambar wajib diunggah.',
            'image.image'            => 'File yang diunggah harus berupa gambar.',
            'image.max'              => 'Ukuran gambar maksimal 2MB.'
        ];
    }
}
