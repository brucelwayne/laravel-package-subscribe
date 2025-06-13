<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $table = 'blw_email_campaigns';

    public function up(): void
    {
        Schema::create($this->table, function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('营销活动名称');
            $table->text('description')->comment('营销活动描述');
            $table->string('subject')->comment('邮件主题');
            $table->longText('template')->comment('邮件内容模板，支持变量占位符');
            $table->enum('status', ['pending', 'sending', 'sent', 'failed'])->default('pending')->comment('活动状态');
            $table->timestamp('scheduled_at')->nullable()->comment('预定发送时间');
            $table->timestamp('sent_at')->nullable()->comment('实际发送完成时间');

            // 新增统计字段
            $table->unsignedInteger('emails_count')->default(0)->comment('计划发送的邮件总数');
            $table->unsignedInteger('success_count')->default(0)->comment('成功发送的邮件数');
            $table->unsignedInteger('fail_count')->default(0)->comment('失败的邮件数');

            $table->timestamps();

            $table->softDeletes()->index();

            $table->index('status');
            $table->index('sent_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->table);
    }
};
