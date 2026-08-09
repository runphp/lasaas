<?php

declare(strict_types=1);

namespace App\Module\Settings;

/**
 * 当前租户模块设置作用域。
 *
 * 租户设置类解析 group 时需要知道当前作用于哪个租户：
 *  - 租户应用上下文：由 ModuleBootLoader::loadTenantModules() 在 TenancyInitialized 时设置
 *  - 中央后台管理租户设置：由 ModuleSettingManager::resolveTenantSettings() 显式设置
 *
 * 注册为 scoped（每请求一个实例），避免在长驻进程/队列中串号。
 */
class ModuleSettingsScope
{
    protected ?string $tenantId = null;

    public function setTenant(?string $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    public function tenantId(): ?string
    {
        return $this->tenantId;
    }

    /**
     * 当前作用域 key：租户上下文返回租户 ID，中央上下文返回 "central"。
     */
    public function key(): string
    {
        return $this->tenantId ?? 'central';
    }

    public function isCentral(): bool
    {
        return $this->tenantId === null;
    }
}
