<?php
namespace App\Services\Tax;

use App\Models\Tax;

class TaxService
{
    public function create(array $data)
    {
        $data['tenant_id'] = tenant()->id;
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        return Tax::create($data);
    }
}
