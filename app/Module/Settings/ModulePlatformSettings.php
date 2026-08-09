<?php

declare(strict_types=1);

namespace App\Module\Settings;

use Spatie\LaravelSettings\Settings;

/**
 * 模块平台设置基类。
 *
 * 模块包内的平台设置类应继承本类：
 *  - public 属性即设置字段，PHP 默认值即默认设置
 *  - groupKey() 返回设置分组 key（通常与模块包名一致，如 "lasaas/blog"）
 *  - schema() 返回后台「模块 → 设置」表单的 Filament 组件数组，
 *    字段名与 public 属性一一对应（默认空，无后台表单时可不覆盖）
 *
 * 设置持久化到中央库 settings 表，group 固定为 "module:{groupKey}"，
 * 对所有租户统一生效。运行时通过 ModuleSettingManager::resolvePlatformSettings()
 * 或直接实例化该类读取，不再合并进 config()。
 */
abstract class ModulePlatformSettings extends Settings
{
    public static function group(): string
    {
        return 'module:'.static::groupKey();
    }

    /**
     * 设置分组 key（通常与模块包名一致，如 "lasaas/blog"）。
     */
    abstract public static function groupKey(): string;

    /**
     * 平台设置表单结构（后台「模块 → 设置」页使用）。
     *
     * @return array<int, Filament\Forms\Components\Component>
     */
    public static function schema(): array
    {
        return [];
    }

    /**
     * 持久化设置值。
     *
     * 模块设置以 PHP 默认值作为初始值，未做逐模块 settings 迁移。
     * spatie 在 save() 时会把「仅由 PHP 默认值填充、仓库中尚不存在的属性」视为缺失
     * 并抛出 MissingSettings——这里先收集属性值再清除该标记，使默认值可以正常落库。
     */
    public function save(): self
    {
        $this->toCollection();
        $this->settingsConfig()->resetDefaultValueLoadedProperties();

        return parent::save();
    }
}
