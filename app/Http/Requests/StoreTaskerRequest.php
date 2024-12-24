<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'username' => 'required|min:3|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:8',
            'role' => 'required',
            'firstname' => 'required|string|max:50',
            'lastname' => 'required|string|max:50|',
            'phone' => 'required|regex:/^[0-9]{10,11}$/',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'company' => 'nullable|string|max:255',
            'bio' => 'nullable|string'
        ];
    }
    protected function prepareForValidation()
    {
        $this->merge([
            'role' => $this->role ?? 'taskee',
        ]);
    }
}
