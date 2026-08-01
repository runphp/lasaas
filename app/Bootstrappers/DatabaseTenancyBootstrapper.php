<?php

namespace App\Bootstrappers;

use Stancl\Tenancy\Bootstrappers\DatabaseTenancyBootstrapper as BaseDatabaseTenancyBootstrapper;
use Stancl\Tenancy\Contracts\Tenant;
use Throwable;

class DatabaseTenancyBootstrapper extends BaseDatabaseTenancyBootstrapper
{
    /**
     * 数据库由人工手动创建并配置。
     *
     * 基类在 local 环境下会在初始化时检查数据库是否存在并直接抛异常（500），
     * 导致请求在 EnsureTenantAccessible 中间件执行之前就中断。这里跳过该检查，
     * 由中间件统一校验数据库配置并返回友好提示。
     */
    public function bootstrap(Tenant $tenant)
    {
        try {
            $this->database->connectToTenant($tenant);
        } catch (Throwable) {
            // 数据库未配置或配置异常时不断言初始化失败，
            // 交给 EnsureTenantAccessible 中间件校验并提示。
        }
    }
}
