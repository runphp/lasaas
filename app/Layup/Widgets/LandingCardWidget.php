<?php

declare(strict_types=1);

namespace App\Layup\Widgets;

use Crumbls\Layup\View\BaseWidget;
use Filament\Forms\Components\TextInput;

class LandingCardWidget extends BaseWidget
{
    public static function getType(): string
    {
        return 'landing-card';
    }

    public static function getLabel(): string
    {
        return '特性卡片';
    }

    public static function getIcon(): string
    {
        return 'heroicon-o-squares-2x2';
    }

    public static function getCategory(): string
    {
        return 'landing';
    }

    public static function getContentFormSchema(): array
    {
        return [
            TextInput::make('icon')
                ->label('图标（emoji）')
                ->nullable(),
            TextInput::make('title')
                ->label('标题')
                ->required(),
            TextInput::make('description')
                ->label('描述')
                ->nullable(),
            TextInput::make('color')
                ->label('主题色（blue/green/purple/amber/rose/teal/indigo/orange/zinc）')
                ->default('blue'),
        ];
    }

    public static function getDefaultData(): array
    {
        return [
            'icon' => '',
            'title' => '',
            'description' => '',
            'color' => 'blue',
        ];
    }

    public static function getPreview(array $data): string
    {
        return '📦 '.($data['title'] ?? '(empty card)');
    }

    public function getViewName(): string
    {
        return 'components.layup.landing-card';
    }
}
