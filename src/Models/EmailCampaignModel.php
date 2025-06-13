<?php

namespace Brucelwayne\Subscribe\Models;

use Brucelwayne\Subscribe\Enums\EmailCampaignStatus;
use Illuminate\Support\Carbon;
use Laravel\Scout\Searchable;
use Mallria\Core\Models\BaseMysqlModel;
use MongoDB\Laravel\Eloquent\SoftDeletes;
use Veelasky\LaravelHashId\Eloquent\HashableId;

/**
 * @property int $id
 * @property string $name
 * @property string description
 * @property string $subject
 * @property string $template
 * @property EmailCampaignStatus $status
 * @property Carbon|null $scheduled_at
 * @property Carbon|null $sent_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property int emails_count
 * @property int success_count
 * @property int fail_count
 *
 */
class EmailCampaignModel extends BaseMysqlModel
{
    use HashableId;
    use Searchable;
    use SoftDeletes;

    protected $table = 'blw_email_campaigns';
    protected $hashKey = 'blw_email_campaigns';

    protected $fillable = [
        'name',
        'description',
        'subject',
        'template',
        'status',
        'scheduled_at',
        'sent_at',
        'emails_count',
        'success_count',
        'fail_count',
    ];

    protected $dates = [
        'scheduled_at',
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
        'emails_count' => 'integer',
        'success_count' => 'integer',
        'fail_count' => 'integer',
    ];

    public function getRouteKeyName()
    {
        return 'hash';
    }

    public function logs()
    {
        return $this->hasMany(EmailCampaignLogModel::class, 'campaign_id');
    }

    public function toSearchableArray()
    {
        return $this->toArray();
    }
}
