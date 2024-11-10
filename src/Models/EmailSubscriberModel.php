<?php

namespace Brucelwayne\Subscribe\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Mallria\Core\Models\BaseMysqlModel;
use Spatie\Tags\HasTags;
use Veelasky\LaravelHashId\Eloquent\HashableId;

class EmailSubscriberModel extends BaseMysqlModel
{
    use HashableId;
    use SoftDeletes;
    use HasTags;

    protected $table = 'blw_email_subscribers';

    protected $hashKey = 'blw_email_subscribers';

    protected $appends = [
        'hash',
    ];

    protected $fillable = [
        'email',
    ];

    protected $hidden = [
        'id',
    ];

    static function subscribe($email, string|array|null $tags=[])
    {
        return DB::transaction(function () use ($email, $tags) {
            $key = ['email' => $email];

            //看看是否有删除的，删除的是用户取消订阅的
            /**
             * @var EmailSubscriberModel $model
             */
            $model = self::withTrashed()->where($key)->first();
            if (!empty($model)) {
                //恢复订阅
                $model->restore();
            } else {
                //创建新的订阅
                $model = self::firstOrCreate($key, $key);
            }

            if (!empty($tags)) {
                if (is_string($tags)) {
                    $model->attachTag($tags);
                } else if (is_array($tags)) {
                    $model->attachTags($tags);
                }
            }


            //调用 mailchimp api

            return $model;
        });
    }

    static function unsubscribe($email)
    {

        return DB::transaction(function () use ($email) {
            $key = ['email' => $email];

            /**
             * @var EmailSubscriberModel $model
             */
            $model = self::withTrashed()->where($key)->first();

            if (!empty($model->deleted_at)) {
                return true;
            }

            if ($model->delete()) {
                //取消所有的tags
                $model->tags()->detach();
                return true;
            }
            return false;
        });
    }

    public function getRouteKeyName()
    {
        return 'hash';
    }
}
