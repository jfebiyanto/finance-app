<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where('user_id', $this->user()?->id),
            ],
            'category_name' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', Rule::in(['expense', 'income'])],
            'amount' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:255'],
            'payee' => ['nullable', 'string', 'max:255'],
            'merchant' => ['nullable', 'string', 'max:255'],
            'transaction_date' => ['nullable', 'date'],
        ];
    }

    /**
     * Ensure category_id and category_name are not both provided.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->filled('category_id') && $this->filled('category_name')) {
                $validator->errors()->add('category_name', 'Provide either category_id or category_name, not both.');
            }
        });
    }
}
