<?php

namespace App\Http\Requests\Clerks;

use App\Enums\Roles;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => ['required' , 'string'],
            'email' => ['required' , 'email' , 'unique:users'],
            'password' => ['required' , 'string' , 'confirmed'],
            'roles' => ['required' , 'array' , 'min:1'],
            'roles.*' => [ Rule::In([Roles::Writer->value , Roles::Moderator->value , Roles::Journalist->value])],
            'publisher' => ['boolean'],
        ];
    }
}
