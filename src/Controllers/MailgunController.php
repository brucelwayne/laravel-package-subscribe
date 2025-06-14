<?php

namespace Brucelwayne\Subscribe\Controllers;

use Brucelwayne\Subscribe\Models\EmailWebhookModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Mallria\Core\Http\Controllers\BaseController;

class MailgunController extends BaseController
{
    public function webhook(Request $request)
    {
//        Log::info('Mailgun webhook received', [
//            'request_all' => $request->all()
//        ]);

        $config = config('brucelwayne-subscribe.mailgun', []);
        $webhook_secret = $config['webhook_secret'] ?? null;

        if (empty($webhook_secret)) {
            Log::error('Webhook secret not configured');
            return response()->json(['error' => 'Webhook secret not configured'], 500);
        }

        // ====== 【修改点1】从 signature 数组里读取签名字段 ======
        $signatureData = $request->input('signature', []);
        $timestamp = $signatureData['timestamp'] ?? null;
        $token = $signatureData['token'] ?? null;
        $signature = $signatureData['signature'] ?? null;

//        Log::info('Signature check inputs', compact('timestamp', 'token', 'signature'));

        if (!$timestamp || !$token || !$signature) {
//            Log::warning('Invalid webhook request: missing timestamp/token/signature');
            return response()->json(['error' => 'Invalid webhook request'], 400);
        }

        $signed_data = $timestamp . $token;
        $calculated_signature = hash_hmac('sha256', $signed_data, $webhook_secret);

//        Log::info('Calculated signature', ['calculated_signature' => $calculated_signature]);

        if (!hash_equals($calculated_signature, $signature)) {
//            Log::warning('Invalid signature', [
//                'expected' => $calculated_signature,
//                'actual' => $signature,
//            ]);
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        // ====== 【修改点2】从 event-data 节点取真正的事件数据 ======
        $payload = $request->input('event-data', []);

        // 提取关键字段
        $messageIdRaw = $payload['message']['headers']['message-id'] ?? null;
        $message_id = $messageIdRaw ? trim($messageIdRaw, "<>") : null; // 去除尖括号

        $to_email = $payload['recipient'] ?? null;
        $event = $payload['event'] ?? null;
        $timestamp_event = $payload['timestamp'] ?? null;

//        Log::info('Parsed webhook data', compact('message_id', 'to_email', 'event', 'timestamp_event'));

        if (!$message_id || !$to_email || !$event) {
//            Log::warning('Missing required webhook data', compact('message_id', 'to_email', 'event'));
            return response()->json(['error' => 'Missing required webhook data'], 400);
        }

        $status_map = [
            'delivered' => 'delivered',
            'failed' => 'failed',
            'opened' => 'opened',
            'clicked' => 'clicked',
        ];
        $status = $status_map[$event] ?? 'unknown';

        $event_time = $timestamp_event ? date('Y-m-d H:i:s', $timestamp_event) : now()->toDateTimeString();

//        Log::info('Mapped status and event_time', compact('status', 'event_time'));

//        $cacheKey = 'mailgun_email_webhook_' . $message_id;

        try {
            // 缓存读取
//            $email_webhook_model = Cache::get($cacheKey);

//            if (!$email_webhook_model) {
            $email_webhook_model = EmailWebhookModel::where('provider', 'mailgun')
                ->where('message_id', $message_id)
                ->first();
//            }

            if ($email_webhook_model) {
//                Log::info('Existing email webhook record found', ['id' => $email_webhook_model->id]);

                $toEmails = is_array($email_webhook_model->to_emails) ? $email_webhook_model->to_emails : [];
                $recipientStatuses = is_array($email_webhook_model->recipient_statuses) ? $email_webhook_model->recipient_statuses : [];

                if (!in_array($to_email, $toEmails)) {
                    $toEmails[] = $to_email;
//                    Log::info('Added new recipient to to_emails', ['to_email' => $to_email]);
                }

                if (!isset($recipientStatuses[$to_email])) {
                    $recipientStatuses[$to_email] = [
                        'status' => $status,
                        'event_timeline' => [
                            $event => $event_time,
                        ],
                    ];
//                    Log::info('Initialized recipient status for new to_email', ['to_email' => $to_email]);
                } else {
                    $recipientStatuses[$to_email]['status'] = $status;
                    $recipientStatuses[$to_email]['event_timeline'][$event] = $event_time;
//                    Log::info('Updated recipient status for existing to_email', ['to_email' => $to_email]);
                }

                $updateData = [
                    'to_emails' => $toEmails,
                    'recipient_statuses' => $recipientStatuses,
                    'status' => $status,
                    'payload' => $payload,
                    'delivered_at' => $event === 'delivered' && empty($email_webhook_model->delivered_at)
                        ? $event_time
                        : $email_webhook_model->delivered_at,
                ];

//                Log::info('Updating email webhook record', ['updateData' => $updateData]);

                $email_webhook_model->update($updateData);

//                Cache::put($cacheKey, $email_webhook_model, 300);
            } else {
//                Log::info('Creating new email webhook record');

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
                    'payload' => $payload,
                    'delivered_at' => $event === 'delivered' ? $event_time : null,
                ]);

//                Cache::put($cacheKey, $email_webhook_model, 300);
            }
        } catch (\Throwable $e) {
            Log::error('Exception in webhook cache/database processing', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Internal server error'], 500);
        }

//        Log::info('Webhook processed successfully');

        return response()->json(['message' => 'Webhook processed']);
    }
}
