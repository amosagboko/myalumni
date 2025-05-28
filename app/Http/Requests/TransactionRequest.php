<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'fee_id' => 'required|exists:fee_templates,id',
            'amount' => 'required|numeric|min:0',
            'payment_reference' => 'required|string|max:255|unique:transactions,payment_reference',
            'payment_provider' => 'nullable|string|max:50',
            'payment_provider_reference' => 'nullable|string|max:100',
            'payment_link' => 'nullable|string',
            'payment_details' => 'nullable|array'
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'fee_id.required' => 'Please select a fee.',
            'fee_id.exists' => 'The selected fee is invalid.',
            'amount.required' => 'The amount is required.',
            'amount.numeric' => 'The amount must be a number.',
            'amount.min' => 'The amount must be greater than 0.',
            'payment_reference.required' => 'The payment reference is required.',
            'payment_reference.unique' => 'This payment reference has already been used.',
            'payment_provider.max' => 'The payment provider name is too long.',
            'payment_provider_reference.max' => 'The payment provider reference is too long.',
            'payment_details.array' => 'The payment details must be an array.'
        ];
    }
} 