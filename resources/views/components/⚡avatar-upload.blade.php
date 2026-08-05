<?php
namespace App\Livewire;

use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    // 文件校验：图片、最大2MB
    #[Validate('image|max:2048')]
    public $avatar;

    #[Computed]
    public function previewUrl(): ?string
    {
        // 本地已选图片优先展示临时预览图
        if ($this->avatar) {
            try {
                return $this->avatar->temporaryUrl();
            } catch (\Exception $e) {
                // 临时文件丢失时自动清空，降级读取数据库头像
                $this->avatar = null;
            }
        }

        // 数据库存在头像返回缩略图，无则返回null，flux:avatar自动显示首字母
        $mediaUrl = Auth::user()->getFirstMediaUrl('avatar', 'thumb');
        return $mediaUrl === '' ? null : $mediaUrl;
    }

    public function save(): void
    {
        // 先校验文件合法性
        $this->validate();

        try {
            $user = Auth::user();
            // 清空旧头像
            $user->clearMediaCollection('avatar');
            // 存入媒体库
            $user->addMedia($this->avatar)->toMediaCollection('avatar');

            // 关键：上传完成清空临时文件，避免后续读取丢失报错
            $this->avatar = null;

            $this->dispatch('avatar-updated');
            Flux::toast(variant: 'success', text: __('Avatar uploaded successfully.'));
        } catch (\Exception $e) {
            logger('头像上传失败', ['error' => $e->getMessage()]);
            Flux::toast(variant: 'danger', text: __('Upload failed, please reselect the image.'));
        }
    }

    public function remove()
    {
        try {
            $user = Auth::user();
            $user->clearMediaCollection('avatar');
            // 同时清空当前选中的文件
            $this->avatar = null;

            $this->dispatch('avatar-updated');
            Flux::toast(variant: 'info', text: __('Avatar removed.'));
        } catch (\Exception $e) {
            logger('删除头像失败', ['error' => $e->getMessage()]);
            Flux::toast(variant: 'danger', text: __('Failed to delete avatar.'));
        }
    }
};
?>

<div class="space-y-4">
    <flux:label>{{ __('Avatar') }}</flux:label>

    <div class="flex items-center gap-6 flex-wrap">
        {{-- flux头像组件：有图片展示图片，无图显示自定义首字母，color固定配色 --}}
        <flux:avatar
            class="w-28 h-28 shrink-0 rounded-full overflow-hidden border border-gray-200 dark:border-gray-600"
            :src="$this->previewUrl"
            :name="Auth::user()->name"
            :initials="Auth::user()->initials()"
            color="auto"
        />

        <div class="flex flex-col gap-3 flex-1">
            {{-- Flux免费版文件输入框 --}}
            <flux:input
                wire:model="avatar"
                type="file"
                accept="image/png,image/jpeg,image/webp"
            />

            <flux:text size="sm" class="text-gray-500 dark:text-gray-400">
                {{ __('PNG, JPG, WebP up to 2MB') }}
            </flux:text>

            {{-- 上传错误提示 --}}
            @error('avatar')
            <flux:text class="text-red-500 dark:text-red-400 text-sm">
                {{ $message }}
            </flux:text>
            @enderror

            <div class="flex gap-3 mt-1">
                {{-- 仅选中文件时显示上传按钮 --}}
                @if ($avatar)
                    <flux:button wire:click="save()" variant="primary" size="sm">
                        {{ __('Upload new avatar') }}
                    </flux:button>
                @endif

                {{-- 数据库存在头像时显示删除按钮 --}}
                @if($this->previewUrl)
                    <flux:button
                        wire:click="remove()"
                        variant="danger"
                        size="sm"
                        wire:confirm="{{ __('Are you sure to delete your avatar?') }}"
                    >
                        {{ __('Remove Avatar') }}
                    </flux:button>
                @endif
            </div>
        </div>
    </div>
</div>
