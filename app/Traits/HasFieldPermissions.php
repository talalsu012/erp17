<?php

namespace App\Traits;

use Illuminate\Support\Collection;

trait HasFieldPermissions
{
    public function getFieldPermissions(): Collection
    {
        if ($this->roles->contains('name', 'super_admin')) {
            return \App\Models\Field::all();
        }

        return $this->roles->flatMap->fields->unique('id');
    }

    public function getFieldPermissionsNames(): string
    {
        return $this->getFieldPermissions()->pluck('name')->implode(', ');
    }

    public function hasFieldPermission(string $fieldName): bool
    {
        return $this->getFieldPermissions()->contains('name', $fieldName);
    }
}
