<?php

namespace Brucelwayne\Subscribe\Models;

use Brucelwayne\Subscribe\Enums\EmailSubscribeSource;
use Brucelwayne\Subscribe\Traits\HasEmailSubscribeTags;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Mallria\Analytics\Facades\AnalyticsFacade;
use Mallria\Core\Models\BaseMysqlModel;
use Veelasky\LaravelHashId\Eloquent\HashableId;

/**
 * @property string $email
 */
class EmailSubscriberModel extends BaseMysqlModel
{
    use HashableId;
    use SoftDeletes;
    use HasEmailSubscribeTags;

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

    static function subscribe($email, string|array|null $tags = [], EmailSubscribeSource $source = EmailSubscribeSource::PopupSubscribe)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Invalid email format: $email");
        }

        $model = DB::transaction(function () use ($email, $tags, $source) {
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

            $request = \request();

            EmailSubscribeLogModal::create([
                'email' => $email,
                'ip' => $request->getRealIp(),
                'user_agent' => $request->userAgent(),
                'source' => $source->value,
            ]);


            //TODO: 调用 mailchimp api

            return $model;
        });

        AnalyticsFacade::subscribe([
            'email' => $email,
            'subscriber' => $model,
        ]);
//        event(new SubscribeEvent([
//            'subscriber' => $model,
//        ]));

        return $model;
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
//                $model->tags()->detach();
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
