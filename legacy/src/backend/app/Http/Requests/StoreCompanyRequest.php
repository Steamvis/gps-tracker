<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyRequest extends FormRequest
{
    public function rules()
    {
        return [
            'owner_id'   => 'required|integer|exists:users,id',
            'title'      => 'required|string|min:5',
            'country_id' => 'required|integer|exists:countries,id'
        ];
    }

    public function authorize()
    {
        return true;
    }
}
