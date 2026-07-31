<?php

use Livewire\Component;
use Illuminate\Support\Collection;

new class extends Component
{
    public Collection $teamTenants;

    public function mount(): void
    {
        $user = auth()->user();
        // 预加载三层关联，避免N+1
        $user->load('currentTeam.tenants.domains');

        $team = $user->currentTeam;
        $this->teamTenants = $team?->tenants ?? collect();
    }
};
?>

<div class="space-y-6 mt-6">
    <h3 class="text-lg font-semibold">当前团队下属租户</h3>

    @if ($teamTenants->isEmpty())
        <div class="text-sm text-neutral-500 dark:text-neutral-400 py-4">
            暂无创建任何租户
        </div>
    @else
        <div class="grid gap-4">
            @foreach ($teamTenants as $tenant)
                @php
                    $domains = $tenant->domains;
                @endphp

                <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
                    <div class="flex flex-col gap-2">
                        {{-- 租户名称 --}}
                        <h4 class="text-lg font-semibold">{{ $tenant->name }}</h4>

                        {{-- 基础信息行 --}}
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-sm">
                            <div>
                                <span class="text-neutral-500 dark:text-neutral-400">邮箱：</span>
                                {{ $tenant->email ?? '-' }}
                            </div>
                            <div>
                                <span class="text-neutral-500 dark:text-neutral-400">联系电话：</span>
                                {{ $tenant->phone ?? '-' }}
                            </div>
                            <div>
                                <span class="text-neutral-500 dark:text-neutral-400">状态：</span>
                                <span class="{{ $tenant->status->getColorClass() }}">
                                    {{ $tenant->status->getLabel() }}
                                </span>
                            </div>
                            <div>
                                <span class="text-neutral-500 dark:text-neutral-400">到期时间：</span>
                                {{ $tenant->expired_at?->format('Y-m-d H:i') ?? '永久有效' }}
                            </div>
                            <div>
                                <span class="text-neutral-500 dark:text-neutral-400">创建时间：</span>
                                {{ $tenant->created_at->format('Y-m-d H:i') }}
                            </div>
                        </div>

                        {{-- 描述 --}}
                        @if (!empty($tenant->description))
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">
                                备注：{{ $tenant->description }}
                            </p>
                        @endif

                        {{-- 绑定域名列表，带跳转链接 --}}
                        @if ($domains->isNotEmpty())
                            <div class="text-xs text-neutral-400 mt-1">
                                绑定域名：
                                @foreach ($domains as $item)
                                    <a href="https://{{ $item->domain }}/admin" target="_blank" class="mr-2 text-blue-500 hover:underline">
                                        {{ $item->domain }}
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="text-xs text-red-400 mt-1">未绑定域名</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
