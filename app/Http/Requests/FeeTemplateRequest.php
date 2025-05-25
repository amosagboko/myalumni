<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FeeTemplateRequest extends FormRequest
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
            'alumni_year_id' => 'required|exists:alumni_years,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'is_active' => 'boolean'
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
            'alumni_year_id.required' => 'The alumni year is required.',
            'alumni_year_id.exists' => 'The selected alumni year is invalid.',
            'name.required' => 'The name field is required.',
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name may not be greater than 255 characters.',
            'description.required' => 'The description field is required.',
            'description.string' => 'The description must be a string.',
            'amount.required' => 'The amount field is required.',
            'amount.numeric' => 'The amount must be a number.',
            'amount.min' => 'The amount must be at least 0.',
            'due_date.required' => 'The due date is required.',
            'due_date.date' => 'The due date must be a valid date.',
        ];
    }
} 