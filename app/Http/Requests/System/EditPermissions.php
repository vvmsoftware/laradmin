<?php

namespace App\Http\Requests\System;

use Illuminate\Foundation\Http\FormRequest;

class EditPermissions extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->can('users edit');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'permissions' => ['array','nullable'],
            'permissions.*' => ['exists:permissions,name'],
            'roles' => ['array', 'required'],
            'roles.*' => ['exists:roles,name'],
        ];
    }
}
