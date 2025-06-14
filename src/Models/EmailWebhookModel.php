<?php

namespace Brucelwayne\Subscribe\Models;

use Illuminate\Support\Carbon;
use Mallria\Core\Models\BaseMysqlModel;

/**
 * Class EmailWebhookModel
 *
 * @package Brucelwayne\Subscribe\Models
 *
 * @property int $id
 * @property string $provider 邮件服务提供者，例：smtp, mailgun, postmark, sendgrid, ses, sparkpost
 * @property string $message_id 邮件唯一标识（provider 内唯一）
 * @property string $to_emails 收件人邮箱
 * @property string|null $status 邮件状态，如 delivered, failed, opened 等
 * @property array|null $payload webhook 原始内容（JSON解析后的数组）
 * @property array recipient_statuses
 * @property Carbon|null $delivered_at 邮件实际投递时间
 * @property Carbon|null $created_at 创建时间
 * @property Carbon|null $updated_at 更新时间
 */
class EmailWebhookModel extends BaseMysqlModel
{
    protected $table = 'blw_email_webhooks';

    protected $fillable = [
        'provider',
        'message_id',
        'to_emails',
        'status',
        'payload',
        'delivered_at',
        'recipient_statuses',
    ];

    protected $casts = [
        'to_emails' => 'array',
        'recipient_statuses' => 'array',
        'payload' => 'array',
        'delivered_at' => 'datetime',
    ];
}
