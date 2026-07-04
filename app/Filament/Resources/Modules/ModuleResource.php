<?php

namespace App\Filament\Resources\Modules;

use App\Filament\Resources\Modules\Pages\ListModules;
use App\Filament\Resources\Modules\Pages\ViewModule;
use App\Filament\Resources\Modules\Schemas\ModuleForm;
use App\Filament\Resources\Modules\Schemas\ModuleInfolist;
use App\Filament\Resources\Modules\Tables\ModulesTable;
use App\Models\Module;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ModuleResource extends Resource
{
    protected static ?string $model = Module::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'package_name'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return "{$record->name}（{$record->package_name}）";
    }

    public static function getNavigationLabel(): string
    {
        return __('filament-resources.module.label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament-resources.system.group');
    }

    public static function getNavigationSort(): ?int
    {
        return 5;
    }

    public static function form(Schema $schema): Schema
    {
        return ModuleForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ModuleInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ModulesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListModules::route('/'),
            'view' => ViewModule::route('/{record}'),
        ];
    }
}
