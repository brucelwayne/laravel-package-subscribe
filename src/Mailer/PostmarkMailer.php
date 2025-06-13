<?php

namespace Brucelwayne\Subscribe\Mailer;

use Brucelwayne\Subscribe\Contracts\MailerContract;
use Brucelwayne\Subscribe\Jobs\PostmarkSingleJob;
use Brucelwayne\Subscribe\Models\EmailCampaignModel;
use Brucelwayne\Subscribe\Objects\RecipientData;

class PostmarkMailer implements MailerContract
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param EmailCampaignModel $campaign 哪个邮件营销任务
     * @param RecipientData[] $recipients
     * @return bool
     */
    public function sendBatch(EmailCampaignModel $campaign, array $recipients): bool
    {
        foreach ($recipients as $recipient) {
            PostmarkSingleJob::dispatch(
                $this->config,
                $campaign,
                $recipient
            )->onQueue('email');
        }

        return true;
    }
}
