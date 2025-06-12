<?php

namespace Brucelwayne\Subscribe\Models;

use Mallria\Core\Models\BaseMysqlModel;
use Veelasky\LaravelHashId\Eloquent\HashableId;

/**
 * @property int $id
 * @property string $name
 */
class TagModel extends BaseMysqlModel
{
    use HashableId;

    protected $table = 'blw_tags';
    protected $hashKey = 'blw_tags';

    protected $fillable = ['name'];

    protected $appends = [
        'hash',
    ];

    protected $hidden = [
        'id',
    ];

    public function getRouteKeyName()
    {
        return 'hash';
    }
}
