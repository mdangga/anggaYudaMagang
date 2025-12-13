<?php

namespace App\Http\Requests\Location;

use Illuminate\Foundation\Http\FormRequest;

class storeLocationRequest extends FormRequest
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
            'nim'            => [
                'required',
                'string',
                'max:10',
                'unique:locations,nim'
            ],
            'name_location'  => [
                'required',
                'string',
                'max:255',
                'unique:locations,name_location'
            ],
            'description'    => 'required|string',
            'contact' => [
                'required',
                'string',
                'regex:/^(?:\+62|62|0)8[0-9]{7,11}$/'
            ],
            'latitude'       => 'required|numeric',
            'longitude'      => 'required|numeric',
            'images'         => 'nullable|array',
            'images.*'      => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'id_category'    => 'required|exists:categories,id_category',
            'id_department'    => 'required|exists:departments,id_department'
        ];
    }

    public function messages(): array
    {
        return [
            'student_name.required'  => 'Nama mahasiswa wajib diisi.',
            'nim.required'           => 'NIM wajib diisi.',
            'name_location.required' => 'Nama lokasi wajib diisi.',
            'description.required'   => 'Deskripsi wajib diisi.',
            'contact.required'       => 'Kontak wajib diisi.',
            'contact.regex'          => 'Format kontak tidak valid.',
            'latitude.required'      => 'Latitude wajib diisi.',
            'longitude.required'     => 'Longitude wajib diisi.',
            'id_category.required'   => 'Kategori wajib dipilih.',
            'id_department.required'   => 'Departemen wajib dipilih.'
        ];
    }
}
