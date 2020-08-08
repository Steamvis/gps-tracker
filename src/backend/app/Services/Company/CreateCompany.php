<?php

namespace App\Services\Company;

use App\Models\Company;
use App\Services\AbstractBaseService;

class CreateCompany extends AbstractBaseService
{
    public function execute(array $data): Company
    {
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
