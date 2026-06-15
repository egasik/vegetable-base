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
            'price' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'category_id' => ['required', 'exists:categories,id'],
        ];
    }
    public function messages(): array {
        return [
            'name.required' => 'Название товара обязательно.',
            'price.min' => 'Цена должна быть больше 0.',
            'category_id.exists' => 'Выбранная категория не существует.',
        ];
    }
}