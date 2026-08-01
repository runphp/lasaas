<?php

namespace Tests\Fixtures\Modules\PanelExt\Filament\Plugins;

use App\Module\Contracts\AdminPanelPlugin;
use Filament\Panel;

class AdminBlogPlugin implements AdminPanelPlugin
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
