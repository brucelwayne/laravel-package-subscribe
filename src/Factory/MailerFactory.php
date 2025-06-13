<?php

namespace Brucelwayne\Subscribe\Factory;

use Brucelwayne\Subscribe\Contracts\MailerContract;
use Brucelwayne\Subscribe\Mailer\MailgunMailer;
use Brucelwayne\Subscribe\Mailer\PostmarkMailer;

/**
 * 邮件发送器工厂类
 *
 * 根据配置动态创建 Mailgun 或 Postmark 邮件发送器实例
 */
class MailerFactory
{
    /**
     * 创建符合 MailerContract 的邮件发送器实例
     *
     * @return MailerContract
     */
    public static function create(): MailerContract
    {
        /** @var array{engine?: string, mailgun: array<string, string>, postmark: array<string, string>} $config */
        $config = config('brucelwayne-subscribe');
        $engine = $config['engine'] ?? 'mailgun';

        switch ($engine) {
            case 'postmark':
                return new PostmarkMailer($config['postmark']);

            case 'mailgun':
            default:
                return new MailgunMailer($config['mailgun']);
        }
    }
}
