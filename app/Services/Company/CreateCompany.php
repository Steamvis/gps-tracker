<?php

namespace App\Services\Company;

use App\Models\Company;
use App\Services\AbstractBaseService;

class CreateCompany extends AbstractBaseService
{
    public function rules(): array
    {
        return [
            'owner_id'   => 'required|integer|exists:users,id',
            'title'      => 'required|string|min:5',
            'country_id' => 'required|integer|exists:countries,id'
        ];
    }

    public function execute(array $data): Company
    {
        $this->validate($data);

        $company = Company::create([
            'owner_id'   => $data['owner_id'],
            'country_id' => $data['country_id'],
            'title'      => $data['title']
        ]);

        $user = auth()->user();
        $user->company_id = $company->id;
        $user->save();

        return $company;
    }
}
