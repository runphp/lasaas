<?php

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
        Schema::create('tenant_databases', function (Blueprint $table) {
            $table->string('tenant_id')->primary();
            $table->string('connection')->comment('数据库类型模板连接（mariadb/mysql/pgsql/sqlite/sqlsrv），必填');
            $table->string('database')->comment('数据库名（sqlite 为文件名）');
            $table->string('host')->nullable()->comment('主机');
            $table->string('port')->nullable()->comment('端口');
            $table->string('username')->nullable()->comment('用户名');
            $table->text('password')->nullable()->comment('密码（加密存储）');
            $table->string('unix_socket')->nullable();
            $table->string('charset')->nullable();
            $table->string('collation')->nullable();
            $table->string('prefix')->nullable();
            $table->boolean('prefix_indexes')->nullable();
            $table->boolean('strict')->nullable();
            $table->string('engine')->nullable();
            $table->json('options')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_databases');
    }
};
