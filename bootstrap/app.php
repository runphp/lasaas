<?php

use App\Http\Middleware\InitializeTenantAndDispatchRoutes;
use App\Http\Middleware\SetHomeLocale;
use App\Http\Middleware\SetTeamUrlDefaults;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use LaravelLang\Routes\Middlewares\LocalizationByCookie;
use LaravelLang\Routes\Middlewares\LocalizationByHeader;
use LaravelLang\Routes\Middlewares\LocalizationByModel;
use LaravelLang\Routes\Middlewares\LocalizationByParameter;
use LaravelLang\Routes\Middlewares\LocalizationByParameterWithRedirect;
use LaravelLang\Routes\Middlewares\LocalizationBySession;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        RouteServiceProvider::class,
    ])
    ->withRouting(
        // web: __DIR__.'/../routes/web.php', // 中央路由由 RouteServiceProvider::map() 统一延迟注册
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(InitializeTenantAndDispatchRoutes::class);
        $middleware->web(append: [
            SetTeamUrlDefaults::class,
        ])->group('universal', [])->alias([
            'localization.parameter' => LocalizationByParameter::class,
            'localization.redirect' => LocalizationByParameterWithRedirect::class,
            'localization.header' => LocalizationByHeader::class,
            'localization.cookie' => LocalizationByCookie::class,
            'localization.session' => LocalizationBySession::class,
            'localization.model' => LocalizationByModel::class,
            'localization.home' => SetHomeLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
