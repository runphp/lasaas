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
            'sqlsrv' => [
                'database' => 'shop_001',
                'host' => '127.0.0.1',
                'port' => '1433',
                'username' => 'shop_user',
                'password' => '123456',
                'charset' => 'UTF-8',
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
            return 'SQLite 使用本地文件数据库，只需填写“数据库名”（文件名），文件保存在 Laravel 的 database_path() 目录下。';
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
                    ->default(null),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->default(null),
                TextInput::make('phone')
                    ->tel()
                    ->default(null),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('team_id', null)),
                Select::make('team_id')
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
                    ->options(TenantStatus::class)
                    ->default('pending')
                    ->required(),
                DateTimePicker::make('expired_at'),
                Repeater::make('domains')
                    ->relationship('domains')
                    ->schema([
                        TextInput::make('domain')
                            ->required()
                            ->unique('domains', 'domain', ignoreRecord: true)
                            ->helperText('如：myshop.tenant.ddev.site'),
                    ])
                    ->addActionLabel('添加域名')
                    ->collapsible()
                    ->defaultItems(1)
                    ->columnSpanFull(),
                Section::make('数据库连接')
                    ->description('手动指定该租户使用的数据库，数据库需提前创建好')
                    ->relationship('tenantDatabase')
                    ->schema([
                        Select::make('connection')
                            ->label('数据库类型')
                            ->options([
                                'mariadb' => 'MariaDB',
                                'mysql' => 'MySQL',
                                'pgsql' => 'PostgreSQL',
                                'sqlite' => 'SQLite（文件数据库）',
                                'sqlsrv' => 'SQL Server',
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
                            ->label('数据库名')
                            ->required()
                            ->placeholder(fn (Get $get): ?string => self::example($get, 'database'))
                            ->helperText(fn (Get $get): string => self::isSqlite($get)
                                ? 'SQLite 文件数据库，如 database/shop_001.sqlite'
                                : '该数据库需提前在数据库服务器上创建好'),
                        TextInput::make('host')
                            ->label('主机')
                            ->placeholder(fn (Get $get): ?string => self::example($get, 'host'))
                            ->helperText('留空则使用 config/database.php 中该连接的默认配置')
                            ->visible(fn (Get $get): bool => ! self::isSqlite($get)),
                        TextInput::make('port')
                            ->label('端口')
                            ->numeric()
                            ->placeholder(fn (Get $get): ?string => self::example($get, 'port'))
                            ->helperText('留空则使用 config/database.php 中该连接的默认配置')
                            ->visible(fn (Get $get): bool => ! self::isSqlite($get)),
                        TextInput::make('username')
                            ->label('用户名')
                            ->placeholder(fn (Get $get): ?string => self::example($get, 'username'))
                            ->visible(fn (Get $get): bool => ! self::isSqlite($get)),
                        TextInput::make('password')
                            ->label('密码')
                            ->password()
                            ->revealable()
                            ->placeholder(fn (Get $get): ?string => self::example($get, 'password'))
                            ->visible(fn (Get $get): bool => ! self::isSqlite($get)),
                        TextInput::make('charset')
                            ->label('字符集')
                            ->placeholder(fn (Get $get): ?string => self::example($get, 'charset'))
                            ->visible(fn (Get $get): bool => ! self::isSqlite($get)),
                        TextInput::make('collation')
                            ->label('排序规则')
                            ->placeholder(fn (Get $get): ?string => self::example($get, 'collation'))
                            ->visible(fn (Get $get): bool => ! self::isSqlite($get)),
                        Section::make('高级选项')
                            ->description('可选，留空则使用 config/database.php 中该连接的默认配置')
                            ->collapsible()
                            ->collapsed()
                            ->schema([
                                TextInput::make('unix_socket')
                                    ->label('Unix Socket')
                                    ->visible(fn (Get $get): bool => ! self::isSqlite($get)),
                                TextInput::make('prefix')
                                    ->label('表前缀'),
                                Select::make('prefix_indexes')
                                    ->label('索引是否使用前缀')
                                    ->options([
                                        '1' => '使用',
                                        '0' => '不使用',
                                    ])
                                    ->placeholder('使用默认'),
                                Select::make('strict')
                                    ->label('严格模式')
                                    ->options([
                                        '1' => '启用',
                                        '0' => '关闭',
                                    ])
                                    ->placeholder('使用默认'),
                                TextInput::make('engine')
                                    ->label('存储引擎')
                                    ->placeholder('如 InnoDB'),
                                KeyValue::make('options')
                                    ->label('PDO 选项')
                                    ->keyLabel('选项')
                                    ->valueLabel('值')
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
