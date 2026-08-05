<?php

namespace App\Support\Sms;

use Illuminate\Support\Facades\Log;

/**
 * Thin SMS gateway. Integrations stay thin (see CLAUDE.md): by default this
 * just logs the message so the flow is testable end-to-end with zero config.
 *
 * To go live, bind a real driver in a service provider or swap the body of
 * send() for an HTTP call to your provider (e.g. an SMS gateway used by the
 * municipality). Reads its "from"/toggle from config/site.php → tereni.sms.
 */
class SmsSender
{
    public function enabled(): bool
    {
        return (bool) config('site.tereni.sms.enabled', false);
    }

    /** Returns true when the message was handed off (or logged in stub mode). */
    public function send(string $to, string $message): bool
    {
        $to = preg_replace('/[^+\d]/', '', $to) ?? '';

        if ($to === '') {
            return false;
        }

        if (! $this->enabled()) {
            Log::info('[SMS stub] would send', ['to' => $to, 'message' => $message]);

            return true;
        }

        // Real provider integration goes here (phase 2). Until then, log and
        // report so nothing is silently dropped.
        Log::channel(config('site.tereni.sms.log_channel', 'stack'))
            ->info('[SMS] outbound', ['to' => $to, 'message' => $message]);

        return true;
    }
}
