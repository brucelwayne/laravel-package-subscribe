<?php

namespace Brucelwayne\Subscribe\Models;

use Brucelwayne\Subscribe\Enums\EmailSubscribeSource;
use Illuminate\Support\Carbon;
use Mallria\Core\Models\BaseMysqlModel;
use Veelasky\LaravelHashId\Eloquent\HashableId;

/**
 * 订阅日志模型
 *
 * 记录用户每次提交的订阅邮箱及相关请求信息
 *
 * @property int $id
 * @property string $email 订阅者邮箱
 * @property string|null $ip 订阅请求IP地址
 * @property string|null $user_agent 订阅请求的 User-Agent
 * @property EmailSubscribeSource $source
 * @property Carbon|null $created_at 创建时间
 * @property Carbon|null $updated_at 更新时间
 */
class EmailSubscribeLogModal extends BaseMysqlModel
{
    use HashableId;

    protected $table = 'blw_email_subscribe_logs';
    protected $hashKey = 'blw_email_subscribe_logs';

    protected $appends = [
        'hash',
    ];

    protected $fillable = [
        'email',
        'ip',
        'user_agent',
        'source',
    ];

    protected $casts = [
        'source' => EmailSubscribeSource::class,
    ];

    public function getRouteKeyName()
    {
        return 'hash';
    }
}
