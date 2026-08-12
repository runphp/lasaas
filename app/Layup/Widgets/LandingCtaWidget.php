<?php

declare(strict_types=1);

namespace App\Layup\Widgets;

use Crumbls\Layup\View\BaseWidget;
use Filament\Forms\Components\TextInput;

class LandingCtaWidget extends BaseWidget
{
    public static function getType(): string
    {
        return 'landing-cta';
    }

    public static function getLabel(): string
    {
        return 'CTA 横幅';
    }

    public static function getIcon(): string
    {
        return 'heroicon-o-megaphone';
    }

    public static function getCategory(): string
    {
        return 'landing';
    }

    public static function getContentFormSchema(): array
    {
        return [
            TextInput::make('heading')
                ->label('标题')
                ->required()
                ->columnSpanFull(),
            TextInput::make('description')
                ->label('描述')
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
            TextInput::make('contact_label')
                ->label('联系方式标签（如：联系我）')
                ->nullable()
                ->columnSpanFull(),
            TextInput::make('contact_value')
                ->label('联系方式内容（如：微信：runphp）')
                ->nullable()
                ->columnSpanFull(),
        ];
    }

    public static function getDefaultData(): array
    {
        return [
            'heading' => '',
            'description' => '',
            'button_primary_text' => '',
            'button_primary_url' => '#',
            'button_secondary_text' => '',
            'button_secondary_url' => '#',
            'contact_label' => '',
            'contact_value' => '',
        ];
    }

    public static function getPreview(array $data): string
    {
        return '📣 '.($data['heading'] ?? '(empty cta)');
    }

    public function getViewName(): string
    {
        return 'components.layup.landing-cta';
    }
}
