<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id()->comment('自增主键');
            $table->string('package_name')->unique()->comment('Composer 包名，全局唯一，如 my-saas/module-blog');
            $table->string('name')->comment('模块展示名称，如 博客');
            $table->text('description')->nullable()->comment('模块描述');
            $table->string('version', 50)->nullable()->comment('当前版本号，如 1.0.0，由 modules:sync 命令更新');
            $table->string('provider_class')->comment('ServiceProvider 完整类名，ModuleManager 用容器实例化');
            $table->integer('weight')->default(0)->comment('加载权重，越小越先加载，依赖关系满足后的同级排序');
            $table->json('dependencies')->nullable()->comment('依赖模块包名列表（自动从 composer require 中筛选 lasaas-module 得出）');
            $table->json('after')->nullable()->comment('非强依赖但必须在这些模块之后加载，如 ["my-saas/module-seo"]');
            $table->json('areas')->nullable()->comment('生效区域：["central"]仅中央，["tenant"]仅租户，["central","tenant"]两端');
            $table->string('path', 500)->comment('模块磁盘路径，用于拼接 migration、视图路径');
            $table->string('status', 20)->default('active')->comment('全局开关：active 正常，inactive 全局关闭（紧急下线用）');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
