<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array {
    return [
        'name' => ['required', 'string', 'max:255'],
        'description' => ['required', 'string', 'max:2000'],
        'category_id' => ['required', 'exists:categories,id'],
        
        // Розница
        'is_retail' => ['boolean'],
        'retail_price' => ['required_if:is_retail,1', 'nullable', 'numeric', 'min:0.01'],
        'image' => ['nullable', 'file', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
        
        // Опт
        'is_wholesale' => ['boolean'],
        'wholesale_unit_kg' => ['required_if:is_wholesale,1', 'nullable', 'in:10,20,50'],
        'wholesale_price' => ['required_if:is_wholesale,1', 'nullable', 'numeric', 'min:0.01'],
    ];
}
public function messages(): array {
    return [
        'wholesale_unit_kg.in' => 'Оптовая фасовка может быть только 10, 20 или 50 кг.',
        'retail_price.required_if' => 'Укажите розничную цену, если выбрана розничная продажа.',
        'wholesale_price.required_if' => 'Укажите оптовую цену, если выбрана оптовая продажа.',
    ];
}
}