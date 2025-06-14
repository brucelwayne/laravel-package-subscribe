<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $table = 'blw_email_webhooks';

    public function up(): void
    {
        Schema::create($this->table, function (Blueprint $table) {
            $table->id();


            // 邮件服务提供者（推荐加默认值）
            $table->enum('provider', ['smtp', 'mailgun', 'postmark', 'sendgrid', 'ses', 'sparkpost'])
                ->index();
            // 邮件唯一标识（按 provider 内唯一）
            $table->string('message_id');

            // 联合唯一索引（防止不同 provider 冲突）
            $table->unique(['provider', 'message_id']);

            $table->string('status')->nullable()->index()->comment('sent, delivered, failed, opened 等');
            $table->timestamp('delivered_at')->nullable();

            $table->json('to_emails');
            $table->json('payload')->nullable()->comment('webhook 原始内容');
            $table->json('recipient_statuses')->nullable('event的时间');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->table);
    }
};
