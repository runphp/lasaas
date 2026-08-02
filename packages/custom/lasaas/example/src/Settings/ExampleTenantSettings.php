<?php

declare(strict_types=1);

namespace Lasaas\Example\Settings;

use App\Module\Settings\ModuleTenantSettings;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

/**
 * 示例模块租户设置。
 *
 * 声明模块「每个租户可定制」的那部分设置，持久化到 settings 表
 * group 为 "tenant_module:{tenant_id}:lasaas/example"，按租户隔离。
 *
 * 与平台设置同名（如 per_page）的字段读取顺序：租户已存值 > 平台设置值 > PHP 默认值；
 * 保存时只落库被显式修改的字段，未覆盖的租户自动跟随平台设置修改。
 */
class ExampleTenantSettings extends ModuleTenantSettings
{
    public int $per_page = 15;

    public string $accent_color = '#6366f1';

    public bool $show_excerpt = true;

    public static function groupKey(): string
    {
        return 'lasaas/example';
    }

    /**
     * 租户设置表单（后台「租户 → 模块管理」页），字段名对应 public 属性。
     *
     * @return array<int, Filament\Forms\Components\Component>
     */
    public static function schema(): array
    {
        return [
            TextInput::make('per_page')
                ->label('每页条数')
                ->numeric()
                ->default(15),
            ColorPicker::make('accent_color')
                ->label('主题色')
                ->default('#6366f1'),
            Toggle::make('show_excerpt')
                ->label('显示摘要')
                ->default(true),
        ];
    }
}
