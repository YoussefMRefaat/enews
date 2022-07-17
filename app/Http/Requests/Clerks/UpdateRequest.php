<?php

namespace App\Http\Requests\Clerks;

use App\Enums\Roles;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
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
            'name' => ['string' , 'nullable'],
            'email' => ['email' , 'unique:users'],
            'roles' => ['array' , 'min:1'],
            'roles.*' => [Rule::In(Roles::valuesOf(['moderator' , 'writer' , 'journalist']))],
        ];
    }
}
