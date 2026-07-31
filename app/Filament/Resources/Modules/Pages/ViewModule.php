<?php

declare(strict_types=1);

namespace App\Filament\Resources\Modules\Pages;

use App\Filament\Resources\Modules\ModuleResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewModule extends ViewRecord
{
    protected static string $resource = ModuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('settings')
                ->label(__('模块设置'))
                ->icon('heroicon-m-cog-6-tooth')
                ->url(static::getResource()::getUrl('edit', ['record' => $this->getRecord()])),
        ];
    }
}
