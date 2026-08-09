<?php

declare(strict_types=1);

namespace App\Module;

use RuntimeException;

/**
 * 同租户内多个模块注册了相同 method + URI 的路由时抛出。
 *
 * 跨租户路由冲突由「域名 → 租户 → 仅加载该租户启用模块路由」天然消除；
 * 同租户内多模块仍可能冲突，加载时显式抛出以便模块开发者定位。
 */
class TenantRouteConflictException extends RuntimeException {}
