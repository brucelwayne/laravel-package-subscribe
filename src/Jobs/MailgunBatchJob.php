<?php

namespace Brucelwayne\Subscribe\Jobs;


use Brucelwayne\Subscribe\Enums\EmailCampaignStatus;
use Brucelwayne\Subscribe\Models\EmailCampaignLogModel;
use Brucelwayne\Subscribe\Models\EmailCampaignModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Mailgun\Mailgun;

class MailgunBatchJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels, Dispatchable;

    protected EmailCampaignModel $campaign;
    protected array $config;
    protected array $recipients;

    public function __construct(array $config, EmailCampaignModel $campaign, array $recipients)
    {
        $this->config = $config;
        $this->campaign = $campaign;
        $this->recipients = $recipients;
        $this->onQueue('email');
    }

    public function handle(): void
    {
        $mg = Mailgun::create($this->config['api_key']);

        $batchMessage = $mg->messages()->getBatchMessage($this->config['domain']);

        $batchMessage->setClickTracking(true);
        $batchMessage->addCampaignId($this->campaign->hash);

        //是否在当前时间之后且距离不超过72小时
        $now = now();
        $scheduledAt = $this->campaign->scheduled_at;

        if ($scheduledAt && !($scheduledAt instanceof Carbon)) {
            // 如果是字符串，转成Carbon对象
            $scheduledAt = Carbon::parse($scheduledAt);
        }

        if ($scheduledAt && $scheduledAt->diffInHours($now) <= 72) {
            $batchMessage->setDeliveryTime($scheduledAt);
        }

        $batchMessage->setFromAddress(
            $this->config['from']['address'] ?? 'no-reply@' . $this->config['domain'],
            [
                'full_name' => config('app.name'),
            ]
        );

        $batchMessage->setSubject($this->campaign->subject);
        $batchMessage->setHtmlBody($this->campaign->template);
        $batchMessage->addCustomHeader('X-Payload', json_encode([
                'campaign_id' => $this->campaign->hash,
            ]
        ));

        foreach ($this->recipients as $recipient) {
            $batchMessage->addToRecipient(
                $recipient->email,
                $recipient->variables ?? [] // eg: ['full_name' => 'Bruce']
            );
        }

//        $batchMessage->addToRecipient(
//            'flcgame@gmail.com',
//            $recipient->variables ?? []
//        );
//
//        $batchMessage->addToRecipient(
//            'herilan@hotmail.com',
//            $recipient->variables ?? []
//        );

        // 会自动在 1000 个 recipients 时发送请求；你也可以手动 finalize
        $batchMessage->finalize();

        // 可选：记录 message-ids
        $messageIds = $batchMessage->getMessageIds();
        // 你可以 $this->campaign->logMessageIds($messageIds); 之类的

        foreach ($this->recipients as $recipient) {
            if (!empty($recipient->email)) {
                EmailCampaignLogModel::updateOrCreate([
                    'campaign_id' => $this->campaign->getKey(),
                    'email' => $recipient->email,
                ], [
                    'status' => EmailCampaignStatus::Pending,
                    'variables' => $this->recipients,
                ]);
            }
        }

    }
}

