<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:500'],
            'category_id' => ['required', 'exists:categories,id'],
            
            // Розница
            'is_retail' => ['boolean'],
            'retail_price' => ['nullable', 'numeric', 'min:0.01'], // nullable, так как может быть только опт
            'image' => ['nullable', 'file', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
            
            // Опт
            'is_wholesale' => ['boolean'],
            'wholesale_unit_kg' => ['nullable', 'in:10,20,50'],
            'wholesale_price' => ['nullable', 'numeric', 'min:0.01'],
        ];
    }

    public function messages(): array
    {
        return [
            'wholesale_unit_kg.in' => 'Оптовая фасовка может быть только 10, 20 или 50 кг.',
        ];
    }
}