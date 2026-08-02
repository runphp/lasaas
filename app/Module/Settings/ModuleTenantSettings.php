<?php

declare(strict_types=1);

namespace App\Module\Settings;

use Spatie\LaravelSettings\Events\SavingSettings;
use Spatie\LaravelSettings\Events\SettingsSaved;
use Spatie\LaravelSettings\Settings;
use Spatie\LaravelSettings\SettingsMapper;

/**
 * 模块租户设置基类。
 *
 * 模块包内的租户设置类应继承本类，只声明「租户可以定制」的那部分字段：
 *  - public 属性即设置字段，PHP 默认值即兜底默认值
 *  - groupKey() 返回设置分组 key（通常与模块包名一致，如 "lasaas/blog"）
 *  - schema() 返回后台「租户 → 模块管理」表单的 Filament 组件数组，
 *    字段名与 public 属性一一对应（默认空，无后台表单时可不覆盖）
 *
 * 设置持久化到中央库 settings 表，group 为 "tenant_module:{tenant_id}:{groupKey}"，
 * 按租户隔离。group 中的租户 ID 由 ModuleSettingsScope 提供。
 *
 * 与模块（中央）设置的关系——同名覆盖、其余回退（不是继承）：
 *  - 模块设置类声明全部设置项（平台默认值），租户设置类只声明可定制的子集
 *  - 读取时：租户已存值 > 模块设置值（字段同名）> PHP 默认值
 *  - 保存时：只落库「被显式修改」的字段，模块默认/回退值不写进租户行，
 *    使模块设置修改对未覆盖的租户仍然生效
 */
abstract class ModuleTenantSettings extends Settings
{
    /** @var array<string, true> 未存储（回退）字段名 */
    protected array $fallbackFields = [];

    /** @var array<string, mixed> 字段名 => 回退值（模块设置值或 PHP 默认值） */
    protected array $fallbackValues = [];

    public static function group(): string
    {
        return 'tenant_module:'.app(ModuleSettingsScope::class)->key().':'.static::groupKey();
    }

    /**
     * 设置分组 key（通常与模块包名一致，如 "lasaas/blog"）。
     */
    abstract public static function groupKey(): string;

    /**
     * 租户设置表单结构（后台「租户 → 模块管理」页使用）。
     *
     * @return array<int, Filament\Forms\Components\Component>
     */
    public static function schema(): array
    {
        return [];
    }

    /**
     * 记录未存储字段的回退基线。
     *
     * 在实例完成初始加载（loadValues）后调用：仓库中不存在的字段
     * 由 PHP 默认值填充，这里记录其默认值，保存时据此判断是否被显式修改。
     *
     * @param  array<string, mixed>  $rawValues  加载后的原始值（租户已存值 + PHP 默认值）
     */
    public function trackFallbackValues(array $rawValues): void
    {
        $this->fallbackFields = [];
        $this->fallbackValues = [];

        foreach ($this->settingsConfig()->getDefaultValueLoadedProperties() as $field) {
            $this->fallbackFields[$field] = true;
            $this->fallbackValues[$field] = $rawValues[$field] ?? null;
        }
    }

    /**
     * 未存储（回退）字段名列表。
     *
     * @return array<int, string>
     */
    public function fallbackFields(): array
    {
        return array_keys($this->fallbackFields);
    }

    /**
     * 更新某字段的回退值（模块设置值与租户字段同名时由 ModuleManager 调用）。
     */
    public function setFallbackValue(string $field, mixed $value): void
    {
        $this->fallbackValues[$field] = $value;
    }

    /**
     * 应用模块（中央）设置的同名回退值。
     *
     * 读取顺序：租户已存值 > 模块设置值（字段同名）> PHP 默认值。
     * 对仓库中未存储、且模块设置存在同名字段的字段：
     *  - 把模块设置值作为当前读取值；
     *  - 同时作为保存时的回退基线，值未被显式修改时不写进租户行，
     *    使模块设置修改对未覆盖的租户仍然生效。
     *
     * @param  iterable<string, mixed>  $centralValues  模块设置类的属性名 => 值
     */
    public function applyCentralFallbacks(iterable $centralValues): void
    {
        // 触发懒加载，得到「未存储（回退）字段」及 PHP 默认值基线
        $loaded = $this->toCollection();

        $this->trackFallbackValues($loaded->all());

        foreach ($centralValues as $name => $value) {
            if (isset($this->fallbackFields[$name])) {
                $this->fallbackValues[$name] = $value;
                $this->{$name} = $value;
            }
        }
    }

    /**
     * 持久化设置值。
     *
     * 只落库「已存储」或「被显式修改」的字段；未存储且值仍等于回退值的字段
     * 不写进租户行（先随完整集合写入，再从仓库删除），使模块设置修改对未覆盖
     * 的租户仍然生效。
     */
    public function save(): self
    {
        $config = $this->settingsConfig();

        // 清空「默认值加载」标记，使 spatie 的 MissingSettings 校验通过（save 需要完整属性集合）
        $config->resetDefaultValueLoadedProperties();

        $collection = $this->toCollection();

        // 未存储（回退）且值未被显式修改（仍等于回退值）的字段：不落库
        $skipped = $collection
            ->filter(fn ($value, string $name): bool => isset($this->fallbackFields[$name])
                && $value == ($this->fallbackValues[$name] ?? null))
            ->keys()
            ->all();

        $properties = $collection->reject(fn ($value, string $name): bool => in_array($name, $skipped, true));

        event(new SavingSettings($properties, $this->originalValues, $this));

        $values = app(SettingsMapper::class)->save(static::class, $collection);

        if (! empty($skipped)) {
            $repository = $config->getRepository();

            foreach ($skipped as $name) {
                $repository->deleteProperty($config->getGroup(), $name);
            }
        }

        $this->fill($values);
        $this->originalValues = $values;

        event(new SettingsSaved($this));

        return $this;
    }
}
