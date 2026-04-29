<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        $role = 'panel_user';

        // Provjerava da li korisnik već ima tu ulogu
        if (! $this->record->hasRole($role)) {
            $this->record->assignRole($role);
        }
    }
}
