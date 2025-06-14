<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $table = 'blw_email_campaign_logs';

    public function up(): void
    {
        Schema::create($this->table, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id');  // 关联campaign表
            $table->string('email');                    // 收件人邮箱
            $table->string('status')->default('pending');

            $table->json('variables')->nullable()->comment('用于渲染模板的变量值');
            $table->json('payload')->nullable();
            $table->timestamp('sent_at')->nullable();  // 实际发送时间
            $table->timestamps();

            $table->unique(['campaign_id', 'email'], 'campaign_email_unique');

            $table->index('status');
            $table->index('sent_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->table);
    }
};
