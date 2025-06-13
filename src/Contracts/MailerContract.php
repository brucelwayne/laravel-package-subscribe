<?php

namespace Brucelwayne\Subscribe\Contracts;

use Brucelwayne\Subscribe\Models\EmailCampaignModel;
use Brucelwayne\Subscribe\Objects\RecipientData;

interface MailerContract
{
    /**
     * 批量发送邮件
     *
     * @param EmailCampaignModel $campaign 哪个邮件营销任务
     * @param RecipientData[] $recipients 收件人列表，每个包含 email 和变量
     * @return bool 发送是否成功
     */
    public function sendBatch(EmailCampaignModel $campaign, array $recipients): bool;
}