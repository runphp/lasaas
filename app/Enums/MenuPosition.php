<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * 前台侧边栏菜单位置枚举。
 *
 * 各模块通过 SidebarMenu::for(MenuPosition::DashboardNav) 注入自己的功能入口，
 * 无需关心侧边栏的渲染细节。每个 case 对应一个独立的应用侧边栏。
 */
enum MenuPosition: string
{
    /** 中央应用个人后台侧边栏（{central_domain}/{current_team}/dashboard） */
    case DashboardNav = 'dashboard_nav';

    /** 租户应用个人后台侧边栏（{tenant_domain}/{current_team}/dashboard） */
    case TenantNav = 'tenant_nav';
}
