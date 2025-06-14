<?php

namespace Brucelwayne\Subscribe\Models;

use Illuminate\Support\Carbon;
use Mallria\Core\Models\BaseMysqlModel;
use Veelasky\LaravelHashId\Eloquent\HashableId;

/**
 * @property int $id
 * @property int $campaign_id
 * @property string $email
 * @property string $status
 *
 * @property string $variables
 * @property array $payload
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
        'sent_at',
        'payload',
    ];

    protected $appends = [
        'hash',
    ];

    protected $hidden = [
        'id',
    ];

    protected $casts = [
        'payload' => 'array',
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
