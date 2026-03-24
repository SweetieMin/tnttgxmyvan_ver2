<?php

namespace App\Validation\Admin\Finance;

use Closure;

class TransactionRules
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public static function rules(): array
    {
        return [
            'transaction_date' => ['required', 'date'],
            'category_id' => ['required', 'exists:categories,id'],
            'transaction_item' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:income,expense'],
            'amount' => [
                'required',
                'string',
                function (string $attribute, mixed $value, Closure $fail): void {
                    $normalizedAmount = preg_replace('/[^\d]/', '', (string) $value) ?? '';

                    if ($normalizedAmount === '') {
                        $fail(__('Amount must be a valid number.'));

                        return;
                    }

                    if ((int) $normalizedAmount < 1) {
                        $fail(__('Amount must be at least 1.'));
                    }
                },
            ],
            'attachment' => ['nullable', 'file', 'max:10240', 'mimes:pdf'],
            'in_charge' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:pending,completed'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function messages(): array
    {
        return [
            'transaction_date.required' => __('Transaction date is required.'),
            'category_id.required' => __('Category is required.'),
            'category_id.exists' => __('Selected category is invalid.'),
            'transaction_item.required' => __('Fund item is required.'),
            'type.required' => __('Transaction type is required.'),
            'amount.required' => __('Amount is required.'),
            'amount.min' => __('Amount must be at least 1.'),
            'attachment.file' => __('Attachment must be a valid file.'),
            'attachment.max' => __('Attachment must not be greater than 10MB.'),
            'attachment.mimes' => __('Attachment must be a PDF file'),
            'status.required' => __('Transaction status is required.'),
        ];
    }
}
