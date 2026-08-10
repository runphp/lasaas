<?php

declare(strict_types=1);

namespace App\Menu;

use App\Enums\MenuPosition;
use Closure;
use Spatie\Menu\Menu;
use Stancl\Tenancy\Tenancy;

/**
 * 侧边栏菜单注册表（单例）。
 *
 * 按 MenuPosition 各维护一个 spatie/menu 菜单，供侧边栏视图统一渲染。
 *
 * 模块在自身 ServiceProvider::boot() 中注入导航入口（闭包延迟到视图渲染时才执行，
 * 此时中央/租户路由已注册，route() 可用）：
 *
 *   app(SidebarMenu::class)->register(MenuPosition::DashboardNav, function (Menu $menu): void {
 *       $menu->add(NavItem::to(route('blog.index'), __('博客'))
 *           ->icon('document-text')
 *           ->group('内容')
 *           ->activeRoute('blog.*'));
 *   });
 *
 * group 取值约定：
 *   - 'Team'：并入「团队」分组（如 Dashboard）
 *   - 'Personal'：并入「个人」分组（如设置）
 *   - 其他字符串：动态分组
 *   - null：无标题分组
 *
 * 框架在调用模块的 registerSidebarMenu() 钩子前会通过 forModule() 记录所属模块；
 * 租户上下文构建菜单时仅包含当前租户已启用的模块项，避免把未启用模块的入口
 * 渲染到租户侧边栏（此时其租户路由也未注册，route() 会抛异常）。
 */
class SidebarMenu
{
    /**
     * @var array<string, Menu>
     */
    protected array $menus = [];

    /**
     * 各位置的注册项，待 build() 时执行。
     *
     * @var array<string, list<array{package: ?string, registrar: callable(Menu): void}>>
     */
    protected array $registrars = [];

    /**
     * 当前钩子执行所属的模块包名（由 forModule() 设置）。
     */
    protected ?string $moduleContext = null;

    /**
     * 各位置的内置项闭包（在模块闭包之前执行）。
     *
     * @var array<string, list<callable(Menu): void>>
     */
    protected array $defaults = [];

    public function __construct(protected Tenancy $tenancy)
    {
        $this->defaults[MenuPosition::DashboardNav->value] = [
            fn (Menu $menu): Menu => $menu->add(
                NavItem::to(route('dashboard'), __('Dashboard'))
                    ->icon('home')
                    ->group('Team')
                    ->activeRoute('dashboard'),
            ),
            fn (Menu $menu): Menu => $menu->add(
                NavItem::to(route('profile.edit'), __('Settings'))
                    ->icon('user-circle')
                    ->group('Personal')
                    ->activeRoute('profile.edit'),
            ),
        ];

        $this->defaults[MenuPosition::TenantNav->value] = [
            fn (Menu $menu): Menu => $menu->add(
                NavItem::to(route('tenant.dashboard'), __('Dashboard'))
                    ->icon('home')
                    ->group('Team')
                    ->activeRoute('tenant.dashboard'),
            ),
            fn (Menu $menu): Menu => $menu->add(
                NavItem::to(route('tenant.profile.edit'), __('Settings'))
                    ->icon('user-circle')
                    ->group('Personal')
                    ->activeRoute('tenant.profile.edit'),
            ),
        ];
    }

    /**
     * 记录并执行当前模块的 registerSidebarMenu() 钩子，期间 register() 的注册项归入该模块。
     */
    public function forModule(?string $package, Closure $callback): void
    {
        $previous = $this->moduleContext;
        $this->moduleContext = $package;

        try {
            $callback();
        } finally {
            $this->moduleContext = $previous;
        }
    }

    /**
     * 注册某位置的导航构建闭包。重复调用按注册顺序追加。
     */
    public function register(MenuPosition $position, callable $registrar): void
    {
        $this->registrars[$position->value][] = [
            'package' => $this->moduleContext,
            'registrar' => $registrar,
        ];
    }

    /**
     * 获取某位置的菜单（构建后）。
     */
    public function for(MenuPosition $position): Menu
    {
        return $this->menus[$position->value] ??= $this->build($position);
    }

    /**
     * 构建并返回某位置的菜单：先执行内置项，再执行模块注册的闭包。
     *
     * 幂等：已构建则直接返回。
     */
    public function build(MenuPosition $position): Menu
    {
        if (isset($this->menus[$position->value])) {
            return $this->menus[$position->value];
        }

        $menu = Menu::new();

        foreach ($this->defaults[$position->value] ?? [] as $default) {
            $default($menu);
        }

        foreach ($this->registrars[$position->value] ?? [] as $entry) {
            if (! $this->shouldInclude($entry['package'])) {
                continue;
            }

            $entry['registrar']($menu);
        }

        return $this->menus[$position->value] = $menu;
    }

    /**
     * 判断某模块的注册项是否应出现在当前上下文的菜单中。
     *
     * - 无模块归属（框架内置或测试直接注册）：始终包含。
     * - 中央上下文（未初始化租户）：包含全部模块。
     * - 租户上下文：仅包含当前租户已启用的模块。
     */
    protected function shouldInclude(?string $package): bool
    {
        if ($package === null) {
            return true;
        }

        if (! $this->tenancy->initialized || $this->tenancy->tenant === null) {
            return true;
        }

        return in_array($package, $this->tenancy->tenant->getEnabledModules(), true);
    }

    /**
     * 清空所有位置的菜单与注册闭包（测试用）。
     */
    public function flush(): void
    {
        $this->menus = [];
        $this->registrars = [];
    }
}
