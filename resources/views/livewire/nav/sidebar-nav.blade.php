<?php

use App\Enums\MenuPosition;
use App\Menu\NavItem;
use App\Menu\SidebarMenu;
use Livewire\Component;

new class extends Component {
    public string $position;

    /**
     * @var list<array{heading: string|null, grid: bool, items: list<array{
     *     icon: string,
     *     url: string,
     *     text: string,
     *     current: bool,
     * }>}>
     */
    public array $groups = [];

    public function mount(string $position): void
    {
        $this->position = $position;
        $this->groups = $this->buildGroups(MenuPosition::from($position));
    }

    /**
     * 将菜单按分组组织，供视图直接渲染。
     *
     * 分组顺序固定：内置「Team」「Personal」在前（若存在），其后为模块注入的
     * 其他动态分组（按注册顺序），未指定分组的项归入无标题分组（heading 为 null）。
     *
     * @return list<array{heading: string|null, grid: bool, items: list<array{
     *     icon: string,
     *     url: string,
     *     text: string,
     *     current: bool,
     * }>}>
     */
    protected function buildGroups(MenuPosition $position): array
    {
        $menu = app(SidebarMenu::class)->for($position);

        $items = collect(iterator_to_array($menu));

        $groups = [];

        foreach (['Team', 'Personal'] as $builtin) {
            $groupItems = $items->filter(fn (NavItem $item): bool => $item->getGroup() === $builtin)->values();

            if ($groupItems->isNotEmpty()) {
                $groups[] = [
                    'heading' => $builtin,
                    'grid' => true,
                    'items' => $groupItems->map(fn (NavItem $item): array => $this->presentItem($item))->all(),
                ];
            }
        }

        $others = $items->reject(
            fn (NavItem $item): bool => in_array($item->getGroup(), ['Team', 'Personal'], true),
        );

        foreach ($others->groupBy(fn (NavItem $item): string => $item->getGroup() ?? '') as $heading => $groupItems) {
            $groups[] = [
                'heading' => $heading !== '' ? $heading : null,
                'grid' => false,
                'items' => $groupItems->map(fn (NavItem $item): array => $this->presentItem($item))->all(),
            ];
        }

        return $groups;
    }

    /**
     * 将 NavItem 转为可安全序列化到 Livewire 属性的数组。
     *
     * @return array{icon: string, url: string, text: string, current: bool}
     */
    protected function presentItem(NavItem $item): array
    {
        return [
            'icon' => $item->getIcon(),
            'url' => $item->url(),
            'text' => $item->text(),
            'current' => $item->isCurrent(),
        ];
    }
}; ?>

<div>
    @foreach ($groups as $group)
        <flux:sidebar.nav>
            <flux:sidebar.group :heading="$group['heading'] !== null ? __($group['heading']) : null" class="{{ $group['grid'] ? 'grid' : '' }}">
                @foreach ($group['items'] as $item)
                    <flux:sidebar.item :icon="$item['icon']" :href="$item['url']" :current="$item['current']" wire:navigate>
                        {{ $item['text'] }}
                    </flux:sidebar.item>
                @endforeach
            </flux:sidebar.group>
        </flux:sidebar.nav>
    @endforeach
</div>
