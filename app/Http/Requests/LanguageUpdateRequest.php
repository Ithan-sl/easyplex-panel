<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LanguageUpdateRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            ' language.id' => 'exists:languages,id',
             'language.iso_639_1' => 'exists:languages,iso_639_1',
            
        ];
    }

    public function messages()
    {
        return [
            'language.id' => 'the language does not exist in the database.',
            'language.id.unique' => 'the language already exists.',
            'language.iso_639_1.unique' => 'the language iso_639_1 already exists.',
        ];
    }
}
