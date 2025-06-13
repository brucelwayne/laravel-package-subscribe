<?php

namespace Brucelwayne\Subscribe\Jobs;

use Brucelwayne\Subscribe\Models\EmailCampaignModel;
use Brucelwayne\Subscribe\Objects\RecipientData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

/**
 * Class PostmarkSingleJob
 *
 * 发送单封个性化邮件到指定 recipient（Postmark 不支持 batch）
 */
class PostmarkSingleJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels, Dispatchable;

    protected EmailCampaignModel $campaign;

    /** @var array<string, string> */
    protected array $config;

    /** @var RecipientData */
    protected RecipientData $recipient;

    /**
     * PostmarkSingleJob constructor.
     *
     * @param array<string, string> $config 邮件配置，如 ['from' => '', 'server_token' => '']
     * @param RecipientData $recipient 收件人对象（含变量）
     */
    public function __construct(array $config, EmailCampaignModel $campaign, RecipientData $recipient)
    {
        $this->config = $config;
        $this->campaign = $campaign;
        $this->recipient = $recipient;
        $this->onQueue('email');
    }

    /**
     * 执行发送逻辑（单封）
     *
     * @return void
     */
    public function handle(): void
    {
        $subject = $this->render($this->campaign->subject, $this->recipient->variables);
        $body = $this->render($this->campaign->template, $this->recipient->variables);

        Http::withHeaders([
            'X-Postmark-Server-Token' => $this->config['server_token'],
            'Accept' => 'application/json',
        ])->post('https://api.postmarkapp.com/email', [
            'From' => $this->config['from'],
            'To' => $this->recipient->email,
            'Subject' => $subject,
            'HtmlBody' => $body,
        ]);
    }

    /**
     * 渲染模板变量
     *
     * @param string $tpl
     * @param array<string, string> $vars
     * @return string
     */
    protected function render(string $tpl, array $vars): string
    {
        foreach ($vars as $key => $value) {
            $tpl = str_replace("{{{$key}}}", $value, $tpl);
        }
        return $tpl;
    }
}
