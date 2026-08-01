<?php

use App\Events\FrontendDashboardCardsCollecting;
use App\Events\FrontendNavigationCollecting;
use Illuminate\Routing\Router;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;

test('frontend navigation event collects module nav items', function () {
    Event::listen(FrontendNavigationCollecting::class, function (FrontendNavigationCollecting $event): void {
        $event->items->push(['label' => '博客', 'url' => '/blog', 'icon' => 'document-text']);
    });

    $items = new Collection;

    event(new FrontendNavigationCollecting($items, 'tenant'));

    expect($items)->toHaveCount(1)
        ->and($items[0]['label'])->toBe('博客')
        ->and($items[0]['url'])->toBe('/blog');
});

test('frontend navigation event carries area', function () {
    $seen = null;

    Event::listen(FrontendNavigationCollecting::class, function (FrontendNavigationCollecting $event) use (&$seen): void {
        $seen = $event->area;
    });

    $items = new Collection;

    event(new FrontendNavigationCollecting($items, 'central'));

    expect($seen)->toBe('central');
});

test('frontend dashboard cards event collects module cards', function () {
    Event::listen(FrontendDashboardCardsCollecting::class, function (FrontendDashboardCardsCollecting $event): void {
        $event->items->push(['title' => '博客', 'description' => '发布文章', 'url' => '/blog']);
    });

    $cards = new Collection;

    event(new FrontendDashboardCardsCollecting($cards, 'tenant'));

    expect($cards)->toHaveCount(1)
        ->and($cards[0]['title'])->toBe('博客')
        ->and($cards[0]['description'])->toBe('发布文章');
});

test('module.enabled middleware alias is registered', function () {
    expect(app()->make(Router::class)->getMiddleware())->toHaveKey('module.enabled');
});
