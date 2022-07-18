<?php

namespace App\Http\Requests\Topics;

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
            'category_id' => ['required' , 'numeric' , 'exists:categories,id'],
            'tags' => [ 'array' , 'min:1'],
            'tags.*' => ['numeric' , 'exists:tags,id'],
            'title' => ['required' , 'string'],
            'body' => ['required' , 'string'],
            'published' => [Rule::requiredIf(auth()->user()->publisher) , 'boolean'],
        ];
    }
}
