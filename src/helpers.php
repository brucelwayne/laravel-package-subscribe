<?php


use Illuminate\Support\Arr;
use NZTim\Mailchimp\MailchimpFacade;

function get_default_mailchimp_list_id()
{
    $lists = MailchimpFacade::getLists();
    $list = Arr::get($lists, 0);
    if (empty($list)) {
        return null;
    }
    $list_id = Arr::get($list, 'id');
    return $list_id;
}

