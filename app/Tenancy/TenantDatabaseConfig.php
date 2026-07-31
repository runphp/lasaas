<?php

namespace App\Tenancy;

use App\Models\TenantDatabase;
use RuntimeException;
use Stancl\Tenancy\DatabaseConfig;

class TenantDatabaseConfig extends DatabaseConfig
{
    protected function record(): ?TenantDatabase
    {
        return $this->tenant->tenantDatabase;
    }

    public function getName(): ?string
    {
        return $this->record()?->database;
    }

    public function getUsername(): ?string
    {
        return $this->record()?->username;
    }

    public function getPassword(): ?string
    {
        return $this->record()?->password;
    }

    public function getTemplateConnectionName(): string
    {
        $record = $this->record();

        if (! $record?->connection) {
            throw new RuntimeException(sprintf(
                '租户 [%s] 未配置数据库连接，请在 tenant_databases 表中填写 connection。',
                $this->tenant->getTenantKey(),
            ));
        }

        return $record->connection;
    }

    public function tenantConfig(): array
    {
        $record = $this->record();

        if (! $record) {
            return [];
        }

        $config = [];

        foreach ([
            'host', 'port', 'unix_socket', 'username', 'password',
            'charset', 'collation', 'prefix', 'prefix_indexes',
            'strict', 'engine', 'options',
        ] as $key) {
            if ($record->getAttribute($key) !== null) {
                $config[$key] = $record->getAttribute($key);
            }
        }

        return $config;
    }

    public function makeCredentials(): void
    {
        // 数据库由人工手动管理，不自动生成凭据。
    }
}
