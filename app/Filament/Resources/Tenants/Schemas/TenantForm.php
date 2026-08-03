<?php

namespace App\Filament\Resources\Tenants\Schemas;

use App\Enums\TeamRole;
use App\Enums\TenantStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class TenantForm
{
    /**
     * 各数据库类型的示例配置，用于表单占位提示。
     *
     * @return array<string, array<string, string>>
     */
    private static function connectionExamples(): array
    {
        return [
            'mariadb' => [
                'database' => 'shop_001',
                'host' => '127.0.0.1',
                'port' => '3306',
                'username' => 'shop_user',
                'password' => '123456',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ],
            'mysql' => [
                'database' => 'shop_001',
                'host' => '127.0.0.1',
                'port' => '3306',
                'username' => 'shop_user',
                'password' => '123456',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ],
            'pgsql' => [
                'database' => 'shop_001',
                'host' => '127.0.0.1',
                'port' => '5432',
                'username' => 'shop_user',
                'password' => '123456',
                'charset' => 'UTF8',
                'collation' => 'en_US.UTF-8',
            ],
            'sqlite' => [
                'database' => 'shop_001.sqlite',
            ],
        ];
    }

    private static function selectedConnection(Get $get): string
    {
        return $get('connection') ?? 'mariadb';
    }

    private static function isSqlite(Get $get): bool
    {
        return self::selectedConnection($get) === 'sqlite';
    }

    private static function example(Get $get, string $key): ?string
    {
        return self::connectionExamples()[self::selectedConnection($get)][$key] ?? null;
    }

    private static function connectionSummary(string $connection): string
    {
        $examples = self::connectionExamples()[$connection] ?? [];

        if ($connection === 'sqlite') {
            return __('filament-resources.tenant.database.hints.connection_sqlite_summary');
        }

        $parts = [];

        foreach ($examples as $key => $value) {
            $parts[] = "{$key}={$value}";
        }

        return '示例：'.implode(' ', $parts);
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('models.tenant.name'))
                    ->default(null),
                TextInput::make('email')
                    ->label(__('models.tenant.email'))
                    ->email()
                    ->default(null),
                TextInput::make('phone')
                    ->label(__('models.tenant.phone'))
                    ->tel()
                    ->default(null),
                Select::make('user_id')
                    ->label(__('models.tenant.user'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('team_id', null)),
                Select::make('team_id')
                    ->label(__('models.tenant.team'))
                    ->relationship('team', 'name', modifyQueryUsing: function (Builder $query, Get $get): Builder {
                        $userId = $get('user_id');

                        if ($userId === null) {
                            return $query->whereRaw('1 = 0');
                        }

                        return $query->whereHas('members', fn (Builder $query) => $query
                            ->where('team_members.user_id', $userId)
                            ->where('team_members.role', TeamRole::Owner->value));
                    })
                    ->searchable()
                    ->preload()
                    ->default(null),
                Select::make('status')
                    ->label(__('models.tenant.status'))
                    ->options(TenantStatus::class)
                    ->default('pending')
                    ->required(),
                DateTimePicker::make('expired_at')
                    ->label(__('models.tenant.expired_at')),
                Repeater::make('domains')
                    ->label(__('models.tenant.domains'))
                    ->relationship('domains')
                    ->schema([
                        TextInput::make('domain')
                            ->required()
                            ->unique('domains', 'domain', ignoreRecord: true)
                            ->helperText(__('filament-resources.tenant.database.hints.domain')),
                    ])
                    ->addActionLabel(__('filament-resources.tenant.database.hints.domain_add'))
                    ->collapsible()
                    ->defaultItems(1)
                    ->columnSpanFull(),
                Section::make(__('filament-resources.tenant.database.section'))
                    ->description(__('filament-resources.tenant.database.description'))
                    ->relationship('tenantDatabase')
                    ->schema([
                        Select::make('connection')
                            ->label(__('models.tenant.database.connection'))
                            ->options([
                                'mariadb' => __('models.tenant.connection_types.mariadb'),
                                'mysql' => __('models.tenant.connection_types.mysql'),
                                'pgsql' => __('models.tenant.connection_types.pgsql'),
                                'sqlite' => __('models.tenant.connection_types.sqlite'),
                            ])
                            ->default('mariadb')
                            ->required()
                            ->live()
                            ->columnSpanFull()
                            ->helperText(fn (Get $get): string => self::connectionSummary(self::selectedConnection($get)))
                            ->afterStateUpdated(function (Set $set, ?string $state): void {
                                if ($state === 'sqlite') {
                                    foreach (['host', 'port', 'username', 'password', 'unix_socket', 'charset', 'collation'] as $field) {
                                        $set($field, null);
                                    }
                                }
                            }),
                        TextInput::make('database')
                            ->label(__('models.tenant.database.database'))
                            ->required()
                            ->placeholder(fn (Get $get): ?string => self::example($get, 'database'))
                            ->helperText(fn (Get $get): string => self::isSqlite($get)
                                ? __('filament-resources.tenant.database.hints.database_sqlite')
                                : __('filament-resources.tenant.database.hints.database_non_sqlite')),
                        TextInput::make('host')
                            ->label(__('models.tenant.database.host'))
                            ->placeholder(fn (Get $get): ?string => self::example($get, 'host'))
                            ->helperText(__('filament-resources.tenant.database.placeholders.host'))
                            ->visible(fn (Get $get): bool => ! self::isSqlite($get)),
                        TextInput::make('port')
                            ->label(__('models.tenant.database.port'))
                            ->numeric()
                            ->placeholder(fn (Get $get): ?string => self::example($get, 'port'))
                            ->helperText(__('filament-resources.tenant.database.placeholders.port'))
                            ->visible(fn (Get $get): bool => ! self::isSqlite($get)),
                        TextInput::make('username')
                            ->label(__('models.tenant.database.username'))
                            ->placeholder(fn (Get $get): ?string => self::example($get, 'username'))
                            ->visible(fn (Get $get): bool => ! self::isSqlite($get)),
                        TextInput::make('password')
                            ->label(__('models.tenant.database.password'))
                            ->password()
                            ->revealable()
                            ->placeholder(fn (Get $get): ?string => self::example($get, 'password'))
                            ->visible(fn (Get $get): bool => ! self::isSqlite($get)),
                        TextInput::make('charset')
                            ->label(__('models.tenant.database.charset'))
                            ->placeholder(fn (Get $get): ?string => self::example($get, 'charset'))
                            ->visible(fn (Get $get): bool => ! self::isSqlite($get)),
                        TextInput::make('collation')
                            ->label(__('models.tenant.database.collation'))
                            ->placeholder(fn (Get $get): ?string => self::example($get, 'collation'))
                            ->visible(fn (Get $get): bool => ! self::isSqlite($get)),
                        Section::make(__('filament-resources.tenant.database.advanced'))
                            ->description(__('filament-resources.tenant.database.advanced_description'))
                            ->collapsible()
                            ->collapsed()
                            ->schema([
                                TextInput::make('unix_socket')
                                    ->label(__('models.tenant.database.unix_socket'))
                                    ->visible(fn (Get $get): bool => ! self::isSqlite($get)),
                                TextInput::make('prefix')
                                    ->label(__('models.tenant.database.prefix')),
                                Select::make('prefix_indexes')
                                    ->label(__('models.tenant.database.prefix_indexes'))
                                    ->options([
                                        '1' => __('models.tenant.database.prefix_indexes_options.1'),
                                        '0' => __('models.tenant.database.prefix_indexes_options.0'),
                                    ])
                                    ->placeholder(__('filament-resources.tenant.database.placeholders.prefix_indexes')),
                                Select::make('strict')
                                    ->label(__('models.tenant.database.strict'))
                                    ->options([
                                        '1' => __('models.tenant.database.strict_options.1'),
                                        '0' => __('models.tenant.database.strict_options.0'),
                                    ])
                                    ->placeholder(__('filament-resources.tenant.database.placeholders.strict')),
                                TextInput::make('engine')
                                    ->label(__('models.tenant.database.engine'))
                                    ->placeholder(__('filament-resources.tenant.database.placeholders.engine')),
                                KeyValue::make('options')
                                    ->label(__('models.tenant.database.options'))
                                    ->keyLabel(__('filament-resources.tenant.database.options_key_label'))
                                    ->valueLabel(__('filament-resources.tenant.database.options_value_label'))
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
