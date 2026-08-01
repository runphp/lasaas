<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Support\Collection;

/**
 * 前台侧边栏导航收集事件。
 *
 * 个人后台（{team}/dashboard）布局渲染侧边栏时触发，监听器向 items 追加导航项。
 * 模块可在其 ServiceProvider::boot() 中监听此事件来挂载前台导航入口。
 *
 * 导航项结构：
 *   ['label' => '博客', 'url' => route('blog.index'), 'icon' => 'document-text', 'group' => '内容']
 *
 * $area 取值：'central'（中央应用个人后台）| 'tenant'（租户应用个人后台）。
 */
class FrontendNavigationCollecting
{
    /**
     * @param  Collection<int, array{label: string, url: string, icon?: string, group?: string}>  $items
     */
    public function __construct(
        public readonly Collection $items,
        public readonly string $area,
    ) {}
}
