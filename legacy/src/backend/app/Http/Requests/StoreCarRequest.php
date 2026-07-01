<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCarRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name'        => 'required|string|min:2|max:255',
            'color'       => 'nullable|string|size:7',
            'vin_number'  => 'nullable|string|min:11|max:17',
            'gov_number'  => 'nullable|string|min:3|max:30',
            'description' => 'nullable|string|min:10|max:500',
            'image_path'  => 'nullable|string',
            'mark_id'     => 'required|integer',
            'year'        => 'nullable|date_format:Y',
        ];
    }

    public function authorize()
    {
        return true;
    }
}
