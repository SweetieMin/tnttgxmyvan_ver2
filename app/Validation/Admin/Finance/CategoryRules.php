<?php

namespace App\Validation\Admin\Finance;

use App\Models\Category;
use Illuminate\Validation\Rule;

class CategoryRules
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public static function rules(?int $categoryId = null): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(Category::class, 'name')->ignore($categoryId),
            ],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function messages(): array
    {
        return [
            'name.required' => __('Category name is required.'),
            'name.unique' => __('This category already exists.'),
        ];
    }
}
