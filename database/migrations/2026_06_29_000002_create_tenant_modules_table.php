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
        Schema::create('tenant_modules', function (Blueprint $table) {
            $table->id()->comment('自增主键');
            $table->string('tenant_id')->comment('租户ID，关联 tenants.id');
            $table->unsignedBigInteger('module_id')->comment('模块ID，关联 modules.id');
            $table->boolean('enabled')->default(true)->comment('是否启用：true 启用，false 禁用（保留数据不删）');
            $table->timestamps();

            $table->unique(['tenant_id', 'module_id'], 'uk_tenant_module');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('module_id')->references('id')->on('modules')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_modules');
    }
};
