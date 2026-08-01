<?php

namespace App\Filament\TenantAdmin\Pages;

use App\Filament\TenantAdmin\Widgets\StatsOverview;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\AccountWidget;

class Dashboard extends BaseDashboard
{
    public function getColumns(): int|array
    {
        return 3;
    }

    public function getWidgets(): array
    {
        return [
            StatsOverview::class,
            AccountWidget::class,
        ];
    }
}
