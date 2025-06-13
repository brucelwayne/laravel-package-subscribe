<?php

namespace Brucelwayne\Subscribe\Enums;

enum EmailCampaignStatus: string
{
    case Pending = 'pending';
    case Sending = 'sending';
    case Sent = 'sent';
    case Failed = 'failed';
}