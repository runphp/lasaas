<?php

declare(strict_types=1);

namespace Lasaas\Example\Settings;

use App\Module\Settings\ModulePlatformSettings;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

/**
 * 示例模块平台设置。
 *
 * 声明模块的「平台级」设置（所有租户统一生效的默认值），
 * 持久化到 settings 表 group 为 "module:lasaas/example"。
 *
 * 与租户设置的关系是「同名覆盖、其余回退」：
 * 租户设置里同名字段（如 per_page）未显式覆盖时，读取本类的值。
 */
class ExamplePlatformSettings extends ModulePlatformSettings
{
    public int $per_page = 15;

    public bool $allow_comments = true;

    public string $display_mode = 'list';

    public static function groupKey(): string
    {
        return 'lasaas/example';
    }

    /**
     * 平台设置表单（后台「模块 → 设置」页），字段名对应 public 属性。
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
            Toggle::make('allow_comments')
                ->label('允许评论')
                ->default(true),
            Select::make('display_mode')
                ->label('展示模式')
                ->options(['list' => '列表', 'grid' => '网格', 'cards' => '卡片'])
                ->default('list'),
        ];
    }
}
