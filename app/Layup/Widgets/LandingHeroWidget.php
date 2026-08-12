<?php

declare(strict_types=1);

namespace App\Layup\Widgets;

use Crumbls\Layup\View\BaseWidget;
use Filament\Forms\Components\TextInput;

class LandingHeroWidget extends BaseWidget
{
    public static function getType(): string
    {
        return 'landing-hero';
    }

    public static function getLabel(): string
    {
        return '落地页 Hero';
    }

    public static function getIcon(): string
    {
        return 'heroicon-o-sparkles';
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
                ->nullable()
                ->columnSpanFull(),
            TextInput::make('heading_line1')
                ->label('主标题第一行')
                ->required()
                ->columnSpanFull(),
            TextInput::make('heading_line2')
                ->label('主标题第二行（渐变强调）')
                ->nullable()
                ->columnSpanFull(),
            TextInput::make('description')
                ->label('副标题')
                ->nullable()
                ->columnSpanFull(),
            TextInput::make('button_primary_text')
                ->label('主按钮文字')
                ->nullable(),
            TextInput::make('button_primary_url')
                ->label('主按钮链接')
                ->nullable(),
            TextInput::make('button_secondary_text')
                ->label('次按钮文字')
                ->nullable(),
            TextInput::make('button_secondary_url')
                ->label('次按钮链接')
                ->nullable(),
            TextInput::make('button_ghost_text')
                ->label('GitHub 按钮文字')
                ->nullable(),
            TextInput::make('button_ghost_url')
                ->label('GitHub 按钮链接')
                ->nullable(),
        ];
    }

    public static function getDefaultData(): array
    {
        return [
            'badge' => '',
            'heading_line1' => '',
            'heading_line2' => '',
            'description' => '',
            'button_primary_text' => '',
            'button_primary_url' => '#',
            'button_secondary_text' => '',
            'button_secondary_url' => '#',
            'button_ghost_text' => '',
            'button_ghost_url' => '',
        ];
    }

    public static function getPreview(array $data): string
    {
        return '🚀 '.($data['heading_line1'] ?? '(empty hero)');
    }

    public function getViewName(): string
    {
        return 'components.layup.landing-hero';
    }
}
