<?php

namespace App\Filament\TenantAdmin\Widgets;

use App\Models\Team;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        return [
            Stat::make('总用户', User::count())
                ->description('注册用户总数')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),

            Stat::make('总团队', Team::count())
                ->description('团队总数')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('primary'),
        ];
    }
}
