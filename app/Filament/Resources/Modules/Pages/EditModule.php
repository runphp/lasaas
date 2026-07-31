<?php

declare(strict_types=1);

namespace App\Filament\Resources\Modules\Pages;

use App\Filament\Resources\Modules\ModuleResource;
use App\Module\ModuleManager;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;

class EditModule extends EditRecord
{
    protected static string $resource = ModuleResource::class;

    public function form(Schema $schema): Schema
    {
        $settingsSchema = app(ModuleManager::class)->centralSettingsSchema($this->getRecord());

        return $schema
            ->components($settingsSchema)
            ->statePath('data.settings');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return $this->getRecord()->settings ?? [];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return ['settings' => $data];
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }
}
