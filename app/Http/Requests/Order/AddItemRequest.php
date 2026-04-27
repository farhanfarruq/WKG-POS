<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class AddItemRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'product_id'   => ['required', 'integer', 'exists:products,id'],
            'quantity'     => ['required', 'integer', 'min:1', 'max:99'],
            'notes'        => ['nullable', 'string', 'max:200'],
            'modifier_ids' => ['nullable', 'array'],
            'modifier_ids.*' => ['integer', 'exists:modifiers,id'],
        ];
    }
}
