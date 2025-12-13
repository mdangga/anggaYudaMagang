<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;

class updateCategoryRequest extends FormRequest
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
            'name_category' => 'required|string|unique:categories,name_category,' . $this->route('id') . ',id_category',
        ];
    }

    public function messages(): array
    {
        return [
            'name_category.required' => 'Nama kategori wajib diisi.',
            'name_category.string' => 'Nama kategori harus berupa teks.',
            'name_category.unique' => 'Nama kategori sudah terdaftar.',
        ];
    }
}
