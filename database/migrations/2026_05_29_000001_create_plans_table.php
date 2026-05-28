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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('套餐名称');
            $table->string('slug')->unique()->comment('URL 标识');
            $table->text('description')->nullable()->comment('描述');
            $table->string('badge')->nullable()->comment('角标，如"推荐"');
            $table->decimal('price', 10, 2)->default(0)->comment('价格');
            $table->decimal('original_price', 10, 2)->nullable()->comment('原价，划线用');
            $table->enum('billing_cycle', ['monthly', 'yearly'])->comment('计费周期');
            $table->json('features')->nullable()->comment('功能限制配置');
            $table->unsignedSmallInteger('sort_order')->default(0)->comment('排序');
            $table->boolean('is_featured')->default(false)->comment('是否推荐');
            $table->boolean('is_active')->default(true)->comment('是否启用');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
