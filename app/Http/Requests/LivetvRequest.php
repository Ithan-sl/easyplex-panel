<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LivetvRequest extends FormRequest
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
            'livetv.name' => 'required',
            'livetv.poster_path' => 'nullable|string',
            'livetv.backdrop_path' => 'nullable|string',
            'livetv.link' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'livetv.name.required' => 'the name is required.',
        ];
    }
}
