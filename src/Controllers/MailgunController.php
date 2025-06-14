<?php

namespace Brucelwayne\Subscribe\Controllers;

use Brucelwayne\Subscribe\Models\EmailCampaignLogModel;
use Brucelwayne\Subscribe\Models\EmailCampaignModel;
use Brucelwayne\Subscribe\Models\EmailWebhookModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Mallria\Core\Http\Controllers\BaseController;

class MailgunController extends BaseController
{
    public function webhook(Request $request)
    {
        $config = config('brucelwayne-subscribe.mailgun', []);
        $webhook_secret = $config['webhook_secret'] ?? null;

        if (empty($webhook_secret)) {
            Log::error('Webhook secret not configured');
            return response()->json(['error' => 'Webhook secret not configured'], 500);
        }

        $signatureData = $request->input('signature', []);
        $timestamp = $signatureData['timestamp'] ?? null;
        $token = $signatureData['token'] ?? null;
        $signature = $signatureData['signature'] ?? null;

        if (!$timestamp || !$token || !$signature) {
            return response()->json(['error' => 'Invalid webhook request'], 400);
        }

        $signed_data = $timestamp . $token;
        $calculated_signature = hash_hmac('sha256', $signed_data, $webhook_secret);

        if (!hash_equals($calculated_signature, $signature)) {
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        $payload = $request->input('event-data', []);

        // 提取关键字段
        $messageIdRaw = $payload['message']['headers']['message-id'] ?? null;
        $message_id = $messageIdRaw ? trim($messageIdRaw, "<>") : null; // 去除尖括号

        $to_email = $payload['recipient'] ?? null;
        $event = $payload['event'] ?? null;
        $timestamp_event = $payload['timestamp'] ?? null;

        if (!$message_id || !$to_email || !$event) {
            return response()->json(['error' => 'Missing required webhook data'], 400);
        }

        $status = $event;
        $event_time = $timestamp_event ? date('Y-m-d H:i:s', $timestamp_event) : now()->toDateTimeString();
        try {
            $email_webhook_model = EmailWebhookModel::where('provider', 'mailgun')
                ->where('message_id', $message_id)
                ->first();
            if ($email_webhook_model) {
                $toEmails = is_array($email_webhook_model->to_emails) ? $email_webhook_model->to_emails : [];
                $recipientStatuses = is_array($email_webhook_model->recipient_statuses) ? $email_webhook_model->recipient_statuses : [];

                if (!in_array($to_email, $toEmails)) {
                    $toEmails[] = $to_email;
                }

                if (!isset($recipientStatuses[$to_email])) {
                    $recipientStatuses[$to_email] = [
                        'status' => $status,
                        'event_timeline' => [
                            $event => $event_time,
                        ],
                    ];
                } else {
                    $recipientStatuses[$to_email]['status'] = $status;
                    $recipientStatuses[$to_email]['event_timeline'][$event] = $event_time;
                }

                $payload['webhook_time'] = now()->toDateTimeString();
                $payload_data = $email_webhook_model->payload ?? [];
                $payload_data[$event] = $payload;
                $updateData = [
                    'to_emails' => $toEmails,
                    'recipient_statuses' => $recipientStatuses,
                    'status' => $status,
                    'payload' => $payload_data,
                    'delivered_at' => $event === 'delivered' && empty($email_webhook_model->delivered_at)
                        ? $event_time
                        : $email_webhook_model->delivered_at,
                ];
                $email_webhook_model->update($updateData);
            } else {
                $payload['webhook_time'] = now()->toDateTimeString();
                $payload_data[$event] = $payload;
                $email_webhook_model = EmailWebhookModel::create([
                    'provider' => 'mailgun',
                    'message_id' => $message_id,
                    'to_emails' => [$to_email],
                    'recipient_statuses' => [
                        $to_email => [
                            'status' => $status,
                            'event_timeline' => [$event => $event_time],
                        ],
                    ],
                    'status' => $status,
                    'payload' => $payload_data,
                    'delivered_at' => $event === 'delivered' ? $event_time : null,
                ]);
            }

            //campaign id
            $campaign_hash = $payload['campaign_id'] ?? null;
            if (!empty($campaign_hash)) {
                $campaign_model = EmailCampaignModel::byHash($campaign_hash);
                if (!empty($campaign_model)) {
                    $email_webhook_model->refresh();
                    foreach ($email_webhook_model->to_emails as $to_email) {
                        $email_campaign_log_model = EmailCampaignLogModel::where([
                            'campaign_id' => $campaign_model->getKey(),
                            'email' => $to_email,
                        ])->first();
                        if (!empty($email_campaign_log_model)) {
                            $payload['webhook_time'] = now()->toDateTimeString();
                            // 读取已有的payload数据
                            $payload_data = $email_campaign_log_model->payload ?? [];
                            // 更新当前事件payload
                            $payload_data[$event] = $payload;

                            $values = [
                                'status' => $event,
                                'payload' => $payload_data,  // 更新payload
                            ];

                            if ($event === 'delivered') {
                                $values['sent_at'] = $event_time;
                            }

                            $email_campaign_log_model->update($values);

                        } else {
                            $payload['webhook_time'] = now()->toDateTimeString();
                            // 新建时初始化payload
                            $payload_data = [
                                $event => $payload,
                            ];

                            EmailCampaignLogModel::create([
                                'campaign_id' => $campaign_model->getKey(),
                                'email' => $to_email,
                                'status' => $event,
                                'payload' => $payload_data,
                                'sent_at' => $event === 'delivered' ? $event_time : null,
                            ]);
                        }
                    }
                }
            }

        } catch (\Throwable $e) {
            Log::error('Exception in webhook cache/database processing', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Internal server error'], 500);
        }
        return response()->json(['message' => 'Webhook processed']);
    }
}
