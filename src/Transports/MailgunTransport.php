<?php

namespace Brucelwayne\Subscribe\Transports;

use Brucelwayne\Subscribe\Enums\EmailCampaignStatus;
use Brucelwayne\Subscribe\Models\EmailWebhookModel;
use Mailgun\Mailgun;
use Mailgun\Message\MessageBuilder;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Email;

class MailgunTransport extends AbstractTransport
{
    protected Mailgun $mailgun;
    protected string $domain;
    protected $config;

    public function __construct(?EventDispatcherInterface $dispatcher = null, ?LoggerInterface $logger = null)
    {
        $config = config('brucelwayne-subscribe');
        $this->config = $config['mailgun'] ?? [];

        if (empty($this->config['api_key']) || empty($this->config['domain'])) {
            throw new \InvalidArgumentException('Mailgun API key or domain is not configured properly.');
        }

        $this->mailgun = Mailgun::create($this->config['api_key']);
        $this->domain = $this->config['domain'];

        parent::__construct($dispatcher, $logger);
    }

    public function __toString(): string
    {
        return 'mailgun';
    }

    /**
     * 真正处理发送逻辑的函数
     */
    protected function doSend(SentMessage $sentMessage): void
    {
        $message = $sentMessage->getOriginalMessage();

        if (!$message instanceof Email) {
            throw new \RuntimeException('Only Email messages are supported.');
        }

        $from = 'no-reply@' . $this->config['domain'];
        $fromName = config('app.name');

        $builder = new MessageBuilder();
        $builder->setFromAddress($from, ['full_name' => $fromName]);

        foreach ($message->getTo() as $to) {
            $toName = $to->getName();
            $toAddress = $to->getAddress();
            if ($toName) {
                $builder->addToRecipient($toAddress, ['full_name' => $toName]);
            } else {
                $builder->addToRecipient($toAddress);
            }
        }

        $builder->setSubject($message->getSubject());

        if ($message->getTextBody()) {
            $builder->setTextBody($message->getTextBody());
        }
        if ($message->getHtmlBody()) {
            $builder->setHtmlBody($message->getHtmlBody());
        }

        $response = $this->mailgun->messages()->send($this->domain, $builder->getMessage());

        $message_id = trim($response->getId(), '<>');

        $payloadHeader = $message->getHeaders()->get('X-Payload');
        $payload = $payloadHeader ? json_decode($payloadHeader->getBody(), true) : [];

        $toEmails = [];
        foreach ($message->getTo() as $to) {
            $toEmails[] = $to->getAddress();
        }

        // 存库时用 json_encode
        EmailWebhookModel::create([
            'provider' => 'mailgun',
            'message_id' => $message_id,
            'to_emails' => json_encode($toEmails),
            'status' => EmailCampaignStatus::Sent,
            'payload' => $payload,
        ]);

    }


}
