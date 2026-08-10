<?php

declare(strict_types=1);

namespace App\Menu;

use Spatie\Menu\Link;

/**
 * 侧边栏导航项 —— 继承 spatie/menu 的 Link，附加侧边栏渲染所需元数据。
 *
 * 模块注入入口：
 *   app(SidebarMenu::class)->for(MenuPosition::DashboardNav)
 *       ->add(NavItem::to(route('blog.index'), __('博客'))
 *           ->icon('document-text')
 *           ->group('内容')
 *           ->activeRoute('blog.*'));
 *
 * 相比直接使用 Link，额外提供：
 * - icon：flux:sidebar.item 图标名（Heroicons）
 * - group：归属分组名（'Team' / 'Personal' / 其他自定义分组 / null 无标题）
 * - activeRoute：路由模式（routeIs 通配符），用于 current 高亮判定
 */
class NavItem extends Link
{
    protected string $icon = '';

    protected ?string $group = null;

    protected string|array|null $activeRoute = null;

    public function icon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function group(?string $group): static
    {
        $this->group = $group;

        return $this;
    }

    public function activeRoute(string|array $route): static
    {
        $this->activeRoute = $route;

        return $this;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function getGroup(): ?string
    {
        return $this->group;
    }

    /**
     * 当前请求是否命中该导航项（用于侧边栏 current 高亮）。
     */
    public function isCurrent(): bool
    {
        if ($this->activeRoute === null) {
            return $this->isActive();
        }

        return request()->routeIs($this->activeRoute);
    }
}
