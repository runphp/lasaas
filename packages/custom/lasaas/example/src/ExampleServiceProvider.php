<?php

declare(strict_types=1);

namespace Lasaas\Example;

use App\Module\ModuleServiceProvider;
use App\Module\Settings\ModulePlatformSettings;
use App\Module\Settings\ModuleTenantSettings;
use Lasaas\Example\Settings\ExamplePlatformSettings;
use Lasaas\Example\Settings\ExampleTenantSettings;

class ExampleServiceProvider extends ModuleServiceProvider
{
    /**
     * 模块默认配置合并进 config()。
     *
     * 运行时通过 config('lasaas.example.*') 读取，
     * 例如 config('lasaas.example.title')。
     *
     * 注意：本文件只处理「静态默认配置」（代码级，不可后台编辑）；
     * 后台可编辑的模块设置请声明 platformSettingsClass()/tenantSettingsClass()
     * 返回设置类，表单结构由设置类自身的 schema() 提供，见下方各方法。
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/example.php',
            'lasaas.example',
        );
    }

    public function boot(): void
    {
        //
    }

    /**
     * 模块设置类声明。
     *
     * 表单结构见各设置类自身的 schema()：
     *  - ExamplePlatformSettings::schema() —— 中央后台「模块 → 设置」页
     *  - ExampleTenantSettings::schema()   —— 中央后台「租户 → 模块管理」页
     *
     * @return array{
     *     platform?: class-string<ModulePlatformSettings>,
     *     tenant?: class-string<ModuleTenantSettings>,
     * }
     */
    public function settingsClasses(): array
    {
        return [
            'platform' => ExamplePlatformSettings::class,
            'tenant' => ExampleTenantSettings::class,
        ];
    }
}
