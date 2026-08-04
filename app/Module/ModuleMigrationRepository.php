<?php

declare(strict_types=1);

namespace App\Module;

use Illuminate\Database\ConnectionResolverInterface as Resolver;
use Illuminate\Database\Migrations\DatabaseMigrationRepository;
use Illuminate\Database\Schema\Blueprint;

/**
 * 模块迁移仓库 —— 读写独立表 module_migrations，按 module_id 隔离。
 *
 * 覆盖 Laravel 默认仓库的关键方法，使其：
 *  - 只查询当前模块（module_id）的迁移记录；
 *  - batch 按模块独立编号，模块间互不干扰；
 *  - 写入时显式携带 module_id；
 *  - 建表时含 module_id 列（租户库缺表时由服务自动建表兜底）。
 */
class ModuleMigrationRepository extends DatabaseMigrationRepository
{
    protected int $moduleId;

    public function __construct(Resolver $resolver, int $moduleId, string $table = 'module_migrations')
    {
        parent::__construct($resolver, $table);

        $this->moduleId = $moduleId;
    }

    /**
     * 获取当前模块 id。
     */
    public function moduleId(): int
    {
        return $this->moduleId;
    }

    /**
     * 查询迁移表时始终限定当前模块。
     */
    protected function table()
    {
        return parent::table()->where('module_id', $this->moduleId);
    }

    /**
     * 记录一条已运行的迁移，显式写入 module_id。
     */
    public function log($file, $batch)
    {
        $this->getConnection()->table($this->table)->insert([
            'module_id' => $this->moduleId,
            'migration' => $file,
            'batch' => $batch,
        ]);
    }

    /**
     * 创建迁移仓库表（含 module_id 列）。
     */
    public function createRepository()
    {
        $schema = $this->getConnection()->getSchemaBuilder();

        $schema->create($this->table, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('module_id')->index();
            $table->string('migration');
            $table->integer('batch');
            $table->unique(['module_id', 'migration']);
        });
    }

    /**
     * 清空当前模块的全部迁移记录（卸载兜底，即使迁移文件已删除）。
     */
    public function purge(): void
    {
        $this->getConnection()->table($this->table)
            ->where('module_id', $this->moduleId)
            ->delete();
    }
}
