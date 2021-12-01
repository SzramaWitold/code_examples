<?php

namespace App\Http\Requests;

use App\Models\Descriptors\UserDescriptor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateUserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<mixed>
     */
    public function rules()
    {
        return [
            'email' => ['required', 'email', 'unique:users,email'],
            'username' => ['required', 'string', 'min:2', 'unique:users,username'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'birth' => ['required', 'date', 'before:today'],
            'gender' => ['required', 'string', Rule::in(UserDescriptor::GENDERS)]
        ];
    }

    /**
     * @return array<mixed>
     */
    public function getInputs(): array
    {
        return [
            'email' => $this->input('email'),
            'username' => $this->input('username'),
            'password' => $this->input('password'),
            'birth' => $this->input('birth'),
            'gender' => $this->input('gender'),
        ];
    }
}
