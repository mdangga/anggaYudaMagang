<?php

namespace App\Http\Requests\Department;

use Illuminate\Foundation\Http\FormRequest;

class storeDepartmentRequest extends FormRequest
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
            'name_department' => 'required|string|unique:departments,name_department',
            'degree_level' => 'required|string',
            'id_faculty' => 'required|exists:faculties,id_faculty',
        ];
    }

    public function messages(): array
    {
        return [
            'name_department.required' => 'Nama jurusan wajib diisi.',
            'name_department.string' => 'Nama jurusan harus berupa teks.',
            'name_department.unique' => 'Nama jurusan sudah ada dalam database.',
            'degree_level.required' => 'Tingkat gelar wajib diisi.',
            'degree_level.string' => 'Tingkat gelar harus berupa teks.',
            'id_faculty.required' => 'Fakultas wajib dipilih.',
            'id_faculty.exists' => 'Fakultas yang dipilih tidak valid.',
        ];
    }
}
