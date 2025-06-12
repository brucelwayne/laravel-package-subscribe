<?php

namespace Brucelwayne\Subscribe\Traits;

use Brucelwayne\Subscribe\Models\TagModel;

trait HasEmailSubscribeTags
{
    /**
     * 绑定一个标签（字符串或EmailTagModel）
     */
    public function attachTag($tag)
    {
        if (is_string($tag)) {
            $tagModel = TagModel::firstOrCreate(['name' => $tag]);
        } else {
            $tagModel = $tag;
        }
        return $this->tags()->syncWithoutDetaching($tagModel);
    }

    /**
     * 订阅者和标签多对多关联
     */
    public function tags()
    {
        return $this->belongsToMany(
            TagModel::class,
            'blw_tag_relations',
            'subscriber_id',
            'tag_id'
        );
    }

    /**
     * 绑定多个标签，$tags可以是字符串数组或EmailTagModel数组
     */
    public function attachTags(array $tags)
    {
        $tagIds = [];

        foreach ($tags as $tag) {
            if (is_string($tag)) {
                $tagModel = TagModel::firstOrCreate(['name' => $tag]);
            } else {
                $tagModel = $tag;
            }
            $tagIds[] = $tagModel->id;
        }

        return $this->tags()->syncWithoutDetaching($tagIds);
    }

    /**
     * 解绑一个标签
     */
    public function detachTag($tag)
    {
        if (is_string($tag)) {
            $tagModel = TagModel::where('name', $tag)->first();
            if (!$tagModel) {
                return false;
            }
            $tag = $tagModel->id;
        } else {
            $tag = $tag->id ?? $tag;
        }
        return $this->tags()->detach($tag);
    }

    /**
     * 解绑所有标签
     */
    public function detachAllTags()
    {
        return $this->tags()->detach();
    }
}
