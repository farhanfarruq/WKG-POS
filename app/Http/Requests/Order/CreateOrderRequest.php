<?php

namespace App\Http\Requests\Order;

use App\Enums\OrderType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Order::class);
    }

    public function rules(): array
    {
        return [
            'shift_id'       => ['required', 'exists:shifts,id'],
            'order_type'     => ['required', Rule::enum(OrderType::class)],
            'table_id'       => ['nullable', 'integer'],
            'customer_name'  => ['nullable', 'string', 'max:100'],
            'customer_phone' => ['nullable', 'string', 'max:20'],
            'notes'          => ['nullable', 'string', 'max:500'],
            'discount_id'    => ['nullable', 'integer', 'exists:discounts,id'],
        ];
    }
}
