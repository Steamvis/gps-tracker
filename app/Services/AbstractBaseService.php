<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;

abstract class AbstractBaseService implements InterfaceBaseService
{
    /**
     * Get the validation rules that apply to the service.
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Validate all data to execute the service.
     * @param array $data
     * @return bool
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validate(array $data): bool
    {
        Validator::make($data, $this->rules())
            ->validate();

        return true;
    }
}
