<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize()
    {
        // Only allow superadmins to submit this form
        return backpack_user() && backpack_user()->role === 'superadmin';
    }

    public function rules()
    {
        $id = $this->get('id') ?? $this->route('user');
        
        $rules = [
            'name' => 'required|min:2|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($id)
            ],
            'role' => 'required|in:admin,superadmin',
        ];

        // Require password when creating a new user
        if (!$id) {
            $rules['password'] = 'required|min:8|confirmed';
        } else {
            $rules['password'] = 'nullable|min:8|confirmed';
        }

        return $rules;
    }
}