<?php

namespace Brucelwayne\Subscribe\Mailer;

use Brucelwayne\Subscribe\Contracts\MailerContract;
use Brucelwayne\Subscribe\Jobs\MailgunBatchJob;
use Brucelwayne\Subscribe\Models\EmailCampaignModel;
use Brucelwayne\Subscribe\Objects\RecipientData;

class MailgunMailer implements MailerContract
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
        // 直接派发 Job，不做任何数据转换
        MailgunBatchJob::dispatch(
            $this->config,
            $campaign,
            $recipients
        )->onQueue('email');

        return true;
    }
}
