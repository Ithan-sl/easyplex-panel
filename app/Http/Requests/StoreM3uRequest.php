<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreM3uRequest extends FormRequest
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
            'm3u' => [
                'required',
                'file',
                'mimes:m3u,m3u8,txt',
                'max:20480', // 20MB max size
            ],
        ];
    }

    public function messages()
    {
        return [
            'm3u.required' => 'An M3U playlist file is required.',
            'm3u.file' => 'The uploaded M3U must be a file.',
            'm3u.mimes' => 'The file must be a valid M3U playlist (m3u, m3u8, or txt format).',
            'm3u.max' => 'The M3U file size must not exceed 2MB.',
        ];
    }
}
