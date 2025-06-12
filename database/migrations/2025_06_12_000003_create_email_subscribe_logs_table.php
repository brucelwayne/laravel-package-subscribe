<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $table = 'blw_email_subscribe_logs';

    public function up(): void
    {
        Schema::create($this->table, function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('email')->index()->comment('订阅邮箱');
            $table->string('ip')->nullable()->comment('订阅时用户IP');
            $table->string('user_agent')->nullable()->comment('用户代理信息');
            $table->tinyInteger('source')->default(1)->comment('订阅来源');

            $table->timestamps(); // 包含 created_at 和 updated_at，主要用 created_at 记录订阅时间
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->table);
    }
};
