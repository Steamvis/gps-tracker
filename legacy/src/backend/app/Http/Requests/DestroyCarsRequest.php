<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DestroyCarsRequest extends FormRequest
{
    public function rules()
    {
        return [
            'action' => 'required|array|exists:cars,id'
        ];
    }

    public function authorize()
    {
        return true;
    }
}
