<?php

namespace App\Http\Middleware;

use LaravelLang\Routes\Middlewares\LocalizationByHeader as BaseHeader;

class LocalizationByHeader extends BaseHeader
{
    // 重写父类的 setLocale
    protected function setLocale(string $locale): void
    {
        // 关键：zh-CN → zh_CN
        $laravelLocale = str_replace('-', '_', $locale);

        parent::setLocale($laravelLocale);
    }
}
