<?php

namespace App\Filament\Widgets;

use App\Models\Plan;
use App\Models\Team;
use App\Models\Tenant;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
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

            Stat::make('总租户', Tenant::count())
                ->description('租户总数')
                ->descriptionIcon('heroicon-m-globe-alt')
                ->color('info'),

            Stat::make('活跃套餐', Plan::active()->count())
                ->description('已启用的套餐')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('warning'),
        ];
    }
}
