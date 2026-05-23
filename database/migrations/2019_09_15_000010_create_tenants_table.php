<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->string('id')->primary();

            // custom columns start
            $table->string('name')->nullable()->comment('租户名称');
            $table->string('email')->nullable()->comment('租户管理员邮箱');
            $table->string('phone')->nullable()->comment('联系电话');
            $table->unsignedBigInteger('user_id')->comment('申请人ID');
            $table->enum('status', [
                'pending',
                'active',
                'suspended',
                'expired',
                'disabled',
            ])->default('pending')->comment('租户状态：pending待审核 active正常 suspended暂停 expired过期 disabled禁用');
            $table->timestamp('expired_at')->nullable()->comment('到期时间');
            // custom columns end

            $table->timestamps();
            $table->json('data')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
}
