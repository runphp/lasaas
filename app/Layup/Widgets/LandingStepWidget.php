<?php

declare(strict_types=1);

namespace App\Layup\Widgets;

use Crumbls\Layup\View\BaseWidget;
use Filament\Forms\Components\TextInput;

class LandingStepWidget extends BaseWidget
{
    public static function getType(): string
    {
        return 'landing-step';
    }

    public static function getLabel(): string
    {
        return '步骤卡';
    }

    public static function getIcon(): string
    {
        return 'heroicon-o-arrow-path';
    }

    public static function getCategory(): string
    {
        return 'landing';
    }

    public static function getContentFormSchema(): array
    {
        return [
            TextInput::make('emoji')
                ->label('图标（emoji）')
                ->nullable(),
            TextInput::make('title')
                ->label('标题')
                ->required(),
            TextInput::make('description')
                ->label('描述')
                ->nullable(),
            TextInput::make('color')
                ->label('主题色（blue/violet/amber/emerald/rose）')
                ->default('blue'),
        ];
    }

    public static function getDefaultData(): array
    {
        return [
            'emoji' => '',
            'title' => '',
            'description' => '',
            'color' => 'blue',
        ];
    }

    public static function getPreview(array $data): string
    {
        return '👣 '.($data['title'] ?? '(empty step)');
    }

    public function getViewName(): string
    {
        return 'components.layup.landing-step';
    }
}
