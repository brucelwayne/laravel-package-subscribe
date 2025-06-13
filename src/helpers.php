<?php

use Illuminate\Support\Arr;
use NZTim\Mailchimp\MailchimpFacade;

if (!function_exists('shouldExcludeEmail')) {
    function shouldExcludeEmail(string $email): bool
    {
        // 如果不是合法 email，直接排除
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        }
        
        foreach (config('brucelwayne-subscribe.exclude_email_patterns', []) as $pattern) {
            if (preg_match($pattern, $email)) {
                return true;
            }
        }

        return false;
    }
}

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

