<?php

declare(strict_types=1);

namespace App\Bootstrappers;

use Illuminate\Session\DatabaseSessionHandler;
use Illuminate\Session\Store;
use Stancl\Tenancy\Contracts\TenancyBootstrapper;
use Stancl\Tenancy\Contracts\Tenant;

class SessionTenancyBootstrapper implements TenancyBootstrapper
{
    public function bootstrap(Tenant $tenant)
    {
        $this->switchSessionConnection();
    }

    public function revert()
    {
        $this->switchSessionConnection();
    }

    private function switchSessionConnection(): void
    {
        $handler = $this->getSessionHandler();

        if (! $handler instanceof DatabaseSessionHandler) {
            return;
        }

        $connectionProperty = new \ReflectionProperty($handler, 'connection');

        $connectionProperty->setValue(
            $handler,
            app('db')->connection(),
        );
    }

    private function getSessionHandler(): mixed
    {
        $manager = app('session');

        $property = new \ReflectionProperty($manager, 'drivers');

        $drivers = $property->getValue($manager);

        $driver = reset($drivers);

        return $driver instanceof Store
            ? $driver->getHandler()
            : null;
    }
}
