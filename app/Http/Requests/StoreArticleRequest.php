<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreArticleRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'is_published' => ['boolean'],
            'blocks' => ['required', 'array', 'min:1'],
            'blocks.*.type' => ['required', Rule::in(['header', 'text', 'image'])],
            'blocks.*.content' => ['nullable', 'string'],
            'blocks.*.file' => ['nullable', 'file', 'mimes:jpeg,png,jpg,webp', 'max:10240'], // 10 МБ
            'blocks.*.existing_image' => ['nullable', 'string'], // для режима редактирования
        ];
    }

    /**
     * Кастомная валидация длины контента в зависимости от типа блока.
     * Это требование ТЗ: заголовок макс 128, текст макс 500.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $blocks = $this->input('blocks', []);
            foreach ($blocks as $i => $block) {
                $type = $block['type'] ?? null;
                $content = $block['content'] ?? '';

                if ($type === 'header') {
                    if (empty($content)) {
                        $validator->errors()->add("blocks.$i.content", "Заголовок блока обязателен.");
                    } elseif (mb_strlen($content) > 128) {
                        $validator->errors()->add("blocks.$i.content", "Заголовок не может быть длиннее 128 символов (сейчас: " . mb_strlen($content) . ").");
                    }
                }

                if ($type === 'text') {
                    if (empty($content)) {
                        $validator->errors()->add("blocks.$i.content", "Текст блока обязателен.");
                    } elseif (mb_strlen($content) > 500) {
                        $validator->errors()->add("blocks.$i.content", "Текст не может быть длиннее 500 символов (сейчас: " . mb_strlen($content) . ").");
                    }
                }

                if ($type === 'image') {
                    // Файл либо новый (file), либо уже сохранён (existing_image)
                    if (empty($block['existing_image']) && !$this->hasFile("blocks.$i.file")) {
                        $validator->errors()->add("blocks.$i.file", "Для блока изображения нужно загрузить файл.");
                    }
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Заголовок статьи обязателен.',
            'blocks.required' => 'Добавьте хотя бы один блок в статью.',
            'blocks.min' => 'Статья должна содержать минимум 1 блок.',
            'blocks.*.file.mimes' => 'Допустимы только изображения: JPEG, PNG, JPG, WEBP.',
            'blocks.*.file.max' => 'Размер изображения не должен превышать 10 МБ.',
        ];
    }
}