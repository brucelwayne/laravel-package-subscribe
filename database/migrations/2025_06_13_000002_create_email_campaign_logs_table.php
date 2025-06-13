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
            $table->json('variables')->nullable()->comment('用于渲染模板的变量值');
            $table->enum('status', ['pending', 'sending', 'sent', 'failed'])->default('pending');
            $table->text('error_message')->nullable(); // 失败原因
            $table->timestamp('sent_at')->nullable();  // 实际发送时间
            $table->timestamps();

            $table->index('campaign_id');
            $table->index('email');
            $table->index('status');
            $table->index('sent_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->table);
    }
};
