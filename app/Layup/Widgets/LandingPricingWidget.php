<?php

declare(strict_types=1);

namespace App\Layup\Widgets;

use App\Livewire\PricingSection;
use Crumbls\Layup\View\BaseLivewireWidget;
use Filament\Forms\Components\TextInput;

class LandingPricingWidget extends BaseLivewireWidget
{
    public static function getType(): string
    {
        return 'landing-pricing';
    }

    public static function getLabel(): string
    {
        return '定价（数据库驱动）';
    }

    public static function getIcon(): string
    {
        return 'heroicon-o-currency-dollar';
    }

    public static function getCategory(): string
    {
        return 'landing';
    }

    public static function getLivewireComponent(): string
    {
        return PricingSection::class;
    }

    public static function getContentFormSchema(): array
    {
        return [
            TextInput::make('heading')
                ->label('标题')
                ->default('Pricing Plans')
                ->columnSpanFull(),
            TextInput::make('description')
                ->label('描述')
                ->default('Choose the perfect plan for your needs')
                ->columnSpanFull(),
        ];
    }

    public static function getDefaultData(): array
    {
        return [
            'heading' => 'Pricing Plans',
            'description' => 'Choose the perfect plan for your needs',
        ];
    }

    public static function getPreview(array $data): string
    {
        return '💳 定价（读取数据库套餐）';
    }
}
