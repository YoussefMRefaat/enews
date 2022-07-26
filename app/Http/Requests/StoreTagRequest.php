<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTagRequest extends FormRequest
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
            'name' => [Rule::requiredIf(function(){ return $this->method() == 'POST'; }) , 'string' , $this->uniqueExcept()]
        ];
    }

    /**
     * Check if the unique rule has exception and return the rule
     *
     * @return string
     */
    private function uniqueExcept(): string
    {
        $rule = 'unique:tags,name';
        if ($this->method() == 'PATCH')
            $rule .= ',' . $this->route('tag')->id;
        return $rule;
    }

}
