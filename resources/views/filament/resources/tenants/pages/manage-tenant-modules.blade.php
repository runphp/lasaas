<x-filament-panels::page>
    <div class="flex flex-col gap-6">
        <div class="text-sm text-gray-500 dark:text-gray-400">
            {{ __('为租户安装、启用或禁用模块。只有中央应用已启用、且支持租户区域的模块才会显示在这里。') }}
        </div>

        {{ $this->table }}
    </div>
</x-filament-panels::page>
