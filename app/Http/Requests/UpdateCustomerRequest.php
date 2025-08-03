<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers,email,' . $this->route('customer')->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'loyalty_points' => 'integer|min:0',
            'status' => 'in:active,inactive',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Customer name is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email is already registered to another customer.',
            'loyalty_points.min' => 'Loyalty points must be greater than or equal to 0.',
            'status.in' => 'Status must be either active or inactive.',
        ];
    }
}