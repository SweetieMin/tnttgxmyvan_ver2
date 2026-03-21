<?php

namespace App\Validation\Admin\Finance;

class TransactionRules
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public static function rules(): array
    {
        return [
            'transaction_date' => ['required', 'date'],
            'transaction_item' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:income,expense'],
            'amount' => ['required', 'integer', 'min:1'],
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
            'transaction_item.required' => __('Fund item is required.'),
            'type.required' => __('Transaction type is required.'),
            'amount.required' => __('Amount is required.'),
            'amount.integer' => __('Amount must be an integer.'),
            'amount.min' => __('Amount must be at least 1.'),
            'attachment.file' => __('Attachment must be a valid file.'),
            'attachment.max' => __('Attachment must not be greater than 10MB.'),
            'attachment.mimes' => __('Attachment must be a PDF file'),
            'status.required' => __('Transaction status is required.'),
        ];
    }
}
