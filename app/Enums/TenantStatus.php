<?php

namespace App\Enums;

enum TenantStatus: string
{
    case PENDING = 'pending';       // 待审核
    case ACTIVE = 'active';         // 正常使用
    case SUSPENDED = 'suspended';   // 已暂停
    case EXPIRED = 'expired';       // 已过期
    case DISABLED = 'disabled';     // 已禁用
}
