<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CertificationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'country_code' => 'required|unique:certifications,country_code,' . $this->request->get('id'),
            'certification' => 'required|string|max:255',
            'meaning' => 'required|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'country_code.required' => 'The country code is required.',
            'country_code.unique' => 'This country code is already in use.',
            'certification.required' => 'The certification name is required.',
            'meaning.required' => 'The meaning of the certification is required.',
        ];
    }
}