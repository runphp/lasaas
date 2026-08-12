<?php

declare(strict_types=1);

namespace App\Layup\Widgets;

use Crumbls\Layup\View\BaseWidget;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class LandingHeadingWidget extends BaseWidget
{
    public static function getType(): string
    {
        return 'landing-heading';
    }

    public static function getLabel(): string
    {
        return '区块标题';
    }

    public static function getIcon(): string
    {
        return 'heroicon-o-tag';
    }

    public static function getCategory(): string
    {
        return 'landing';
    }

    public static function getContentFormSchema(): array
    {
        return [
            TextInput::make('badge')
                ->label('徽章')
                ->nullable(),
            TextInput::make('heading')
                ->label('标题')
                ->required(),
            TextInput::make('description')
                ->label('描述')
                ->nullable(),
            Select::make('alignment')
                ->label('对齐')
                ->options([
                    'center' => '居中',
                    'left' => '左对齐',
                ])
                ->default('center'),
        ];
    }

    public static function getDefaultData(): array
    {
        return [
            'badge' => '',
            'heading' => '',
            'description' => '',
            'alignment' => 'center',
        ];
    }

    public static function getPreview(array $data): string
    {
        return '🏷️ '.($data['heading'] ?? '(empty heading)');
    }

    public function getViewName(): string
    {
        return 'components.layup.landing-heading';
    }
}
