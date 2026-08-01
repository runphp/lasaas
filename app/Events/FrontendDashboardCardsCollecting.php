<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Support\Collection;

/**
 * 前台个人后台模块入口卡片收集事件。
 *
 * 个人后台（{team}/dashboard）首页渲染模块入口网格时触发，监听器向 items 追加卡片。
 * 模块可在其 ServiceProvider::boot() 中监听此事件来挂载模块入口卡片。
 *
 * 卡片结构：
 *   ['title' => '博客', 'description' => '发布文章', 'url' => route('blog.index'), 'icon' => 'document-text']
 *
 * $area 取值：'central'（中央应用个人后台）| 'tenant'（租户应用个人后台）。
 */
class FrontendDashboardCardsCollecting
{
    /**
     * @param  Collection<int, array{title: string, description?: string, url: string, icon?: string}>  $items
     */
    public function __construct(
        public readonly Collection $items,
        public readonly string $area,
    ) {}
}
