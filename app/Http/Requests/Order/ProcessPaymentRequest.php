<?php

namespace App\Http\Requests\Order;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProcessPaymentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'payments'                  => ['required', 'array', 'min:1'],
            'payments.*.method'         => ['required', Rule::enum(PaymentMethod::class)],
            'payments.*.amount'         => ['required', 'numeric', 'min:0.01'],
            'payments.*.reference_number' => ['nullable', 'string'],
        ];
    }
}
