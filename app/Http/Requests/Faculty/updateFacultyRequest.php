<?php

namespace App\Http\Requests\Faculty;

use Illuminate\Foundation\Http\FormRequest;

class updateFacultyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name_faculty' => 'required|string|max:255|unique:faculties,name_faculty,' . $this->route('id') . ',id_faculty',
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
