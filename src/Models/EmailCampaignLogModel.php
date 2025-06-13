<?php

namespace Brucelwayne\Subscribe\Models;

use Brucelwayne\Subscribe\Enums\EmailCampaignStatus;
use Illuminate\Support\Carbon;
use Mallria\Core\Models\BaseMysqlModel;
use Veelasky\LaravelHashId\Eloquent\HashableId;

/**
 * @property int $id
 * @property int $campaign_id
 * @property string $email
 * @property string variables
 * @property EmailCampaignStatus $status
 * @property string|null $error_message
 * @property Carbon|null $sent_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class EmailCampaignLogModel extends BaseMysqlModel
{
    use HashableId;

    protected $table = 'blw_email_campaign_logs';
    protected $hashKey = 'blw_email_campaign_logs';

    protected $fillable = [
        'campaign_id',
        'email',
        'status',
        'error_message',
        'sent_at',
    ];

    protected $dates = [
        'sent_at',
        'created_at',
        'updated_at',
    ];

    protected $appends = [
        'hash',
    ];

    protected $hidden = [
        'id',
    ];

    protected $casts = [
        'status' => EmailCampaignStatus::class,
        'variables' => 'array',
    ];

    public function getRouteKeyName()
    {
        return 'hash';
    }

    public function campaign()
    {
        return $this->belongsTo(EmailCampaignModel::class, 'campaign_id');
    }
}
