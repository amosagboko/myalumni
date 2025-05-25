<?php

namespace App\Http\Requests\Candidate;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Candidate;
use App\Models\Election;

class SuggestAgentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $candidate = $this->route('candidate');
        return $candidate && 
               $candidate->alumni_id === auth()->user()->alumni->id && 
               in_array($candidate->election->status, ['draft', 'accreditation']) &&
               $candidate->agent_status === null;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'agent_email' => [
                'required',
                'email',
                'exists:users,email',
                function ($attribute, $value, $fail) {
                    // Ensure the email is not a system-generated email
                    if (str_ends_with($value, '@alumni.fulafia.edu.ng')) {
                        $fail('System-generated emails cannot be used as agent emails.');
                    }
                }
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'agent_email.required' => 'Please enter an email address.',
            'agent_email.email' => 'Please enter a valid email address.',
            'agent_email.exists' => 'No alumni found with this email address.',
        ];
    }
} 