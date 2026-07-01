<?php

namespace App\Services\Users;

use App\Models\User;
use App\Services\AbstractBaseService;
use Illuminate\Support\Facades\DB;

class CreateUser extends AbstractBaseService
{
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],
            'gender'     => ['required', 'in:male,female'],
            'email'      => ['required', 'string', 'email:rfc,dns', 'max:255', 'unique:users'],
            'password'   => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function execute(array $data): User
    {
        $user = User::create($data);

        DB::table('users_settings')->insert(
            [
                [
                    'user_id'    => $user->id,
                    'setting_id' => 1,
                    'value'      => 1,
                ],
                [
                    'user_id'    => $user->id,
                    'setting_id' => 2,
                    'value'      => 10,
                ],
            ]
        );

        return $user;
    }
}
