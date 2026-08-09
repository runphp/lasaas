<?php

declare(strict_types=1);

namespace App\Module;

use Closure;

/**
 * 中央路由队列 —— 延迟加载中央域名下所有网页路由。
 *
 * CentralRouteManager 将路由文件封装为闭包存入队列（不立即 require），
 * 由 dispatchAll() 统一在中央域名分组内批量执行。
 *
 * 注册为单例：整个应用生命周期内只存在一个队列实例，
 * 保证收集阶段与分发阶段共享同一批闭包。
 */
class CentralRouteQueue
{
    /** @var Closure[] 路由文件加载闭包集合 */
    protected array $loaders = [];

    /**
     * 入队一个路由文件加载闭包。
     */
    public function push(Closure $loader): void
    {
        $this->loaders[] = $loader;
    }

    /**
     * 获取全部已入队的路由加载闭包。
     *
     * @return Closure[]
     */
    public function all(): array
    {
        return $this->loaders;
    }

    /**
     * 判断队列是否为空。
     */
    public function isEmpty(): bool
    {
        return empty($this->loaders);
    }

    /**
     * 清空队列。
     */
    public function flush(): void
    {
        $this->loaders = [];
    }
}
