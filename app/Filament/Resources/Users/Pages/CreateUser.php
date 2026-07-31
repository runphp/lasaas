<?php

namespace App\Filament\Resources\Users\Pages;

use App\Actions\Teams\CreateTeam;
use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        $this->record->syncRoles($this->data['roles'] ?? []);

        app(CreateTeam::class)->handle($this->record, "{$this->record->name}'s Team", isPersonal: true);
    }
}
