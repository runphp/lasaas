<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_migrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('module_id')->index()->comment('中央库 modules.id');
            $table->string('migration')->comment('迁移文件名');
            $table->integer('batch')->comment('模块内独立编号的批次');
            $table->unique(['module_id', 'migration']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_migrations');
    }
};
