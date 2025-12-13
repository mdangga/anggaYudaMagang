<?php

namespace App\Http\Requests\Faculty;

use Illuminate\Foundation\Http\FormRequest;

class storeFacultyRequest extends FormRequest
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
            'name_faculty' => 'required|string|max:255|unique:faculties,name_faculty',
        ];
    }

    public function messages(): array
    {
        return [
            'name_faculty.required' => 'Nama fakultas wajib diisi.',
            'name_faculty.string' => 'Nama fakultas harus berupa teks.',
            'name_faculty.max' => 'Nama fakultas tidak boleh lebih dari 255 karakter.',
            'name_faculty.unique' => 'Nama fakultas sudah terdaftar.',
        ];
    }
}
