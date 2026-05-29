<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use LaravelLang\Routes\Middlewares\LocalizationByHeader as BaseHeader;

class SetHomeLocale extends BaseHeader
{
    protected function detect(Request $request): bool|float|int|string|null
    {
        // 1. URL 有 locale 参数 → 优先使用
        if ($request->route() && $request->route()->hasParameter($this->names()->parameter)) {
            return $request->route()->parameter($this->names()->parameter);
        }

        // 2. URL 没有 locale → 从 session 获取
        if ($request->hasSession()) {
            $sessionLocale = $request->getSession()->get($this->names()->session);
            if ($sessionLocale) {
                return $sessionLocale;
            }
        }

        // 3. Session 也没有 → 降级到浏览器 header
        return parent::detect($request);
    }
}
