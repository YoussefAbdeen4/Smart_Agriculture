<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only allow users to update their own profile
        return $this->user()->id === (int) $this->route('user') || ! $this->route('user');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,'.$userId],
            'phone' => ['sometimes', 'string', 'regex:/^[\d\+\-\s\(\).]*$/', 'max:20'],
            'img' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'first_name.string' => 'The first name must be a valid string.',
            'first_name.max' => 'The first name may not be greater than 255 characters.',
            'last_name.string' => 'The last name must be a valid string.',
            'last_name.max' => 'The last name may not be greater than 255 characters.',
            'email.unique' => 'This email address is already in use.',
            'email.email' => 'The email must be a valid email address.',
            'phone.regex' => 'The phone number format is invalid.',
            'phone.max' => 'The phone number may not be greater than 20 characters.',
            'img.image' => 'The uploaded file must be an image.',
            'img.mimes' => 'The image must be a file of type: jpeg, png, jpg, gif, or webp.',
            'img.max' => 'The image may not be greater than 2048 kilobytes.',
        ];
    }
}
