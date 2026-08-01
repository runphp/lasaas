<?php

namespace Tests\Fixtures\Modules\PanelExt\Filament\Plugins;

use App\Module\Contracts\TenantAdminPanelPlugin;
use Filament\Panel;

class TenantBlogPlugin implements TenantAdminPanelPlugin
{
    public function getId(): string
    {
        return 'test-panel-ext-module';
    }

    public function register(Panel $panel): void
    {
        //
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
