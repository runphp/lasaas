<?php

namespace App\Filament\Resources\Tenants\Pages;

use App\Filament\Resources\Tenants\TenantResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewTenant extends ViewRecord
{
    protected static string $resource = TenantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('modules')
                ->label(__('模块管理'))
                ->icon(Heroicon::Squares2x2)
                ->url(static::getResource()::getUrl('modules', ['record' => $this->getRecord()])),
            EditAction::make(),
        ];
    }
}
