<?php
/**
 * Lightweight email sender used across the site (welcome emails, application
 * status updates, withdrawal/wallet confirmations).
 *
 * Sends via Brevo's HTTPS Transactional Email API instead of SMTP — Railway
 * (and most cloud hosts on their free/hobby tiers) blocks outbound SMTP
 * ports (25/465/587) entirely, so PHPMailer-over-SMTP hangs and times out.
 * A plain HTTPS POST request is not affected by that block at all.
 *
 * Needs a Brevo API key set as the BREVO_API_KEY environment variable
 * (Brevo dashboard > SMTP & API > API Keys > Generate a new API key —
 * different from the SMTP key you may have generated earlier).
 *
 * If BREVO_API_KEY hasn't been configured yet, send_email() just returns
 * false and logs a note — it never throws or breaks the page that called it.
 */

/**
 * Send an HTML email. Returns true on success, false if the API key isn't
 * configured yet or sending failed (never throws).
 */
function send_email(string $toEmail, string $toName, string $subject, string $htmlBody): bool {
    $apiKey = defined('BREVO_API_KEY') ? BREVO_API_KEY : '';
    if ($apiKey === '') {
        error_log('[mailer] BREVO_API_KEY not set — skipped email "' . $subject . '" to ' . $toEmail);
        return false;
    }

    $payload = [
        'sender'      => ['email' => SMTP_FROM, 'name' => SMTP_FROM_NAME],
        'to'          => [['email' => $toEmail, 'name' => $toName]],
        'subject'     => $subject,
        'htmlContent' => email_wrap($subject, $htmlBody),
        'textContent' => strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n", $htmlBody)),
    ];

    $ch = curl_init('https://api.brevo.com/v3/smtp/email');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => [
            'accept: application/json',
            'content-type: application/json',
            'api-key: ' . $apiKey,
        ],
        CURLOPT_TIMEOUT        => 15,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        error_log('[mailer] cURL error sending to ' . $toEmail . ': ' . $curlErr);
        return false;
    }
    if ($httpCode >= 200 && $httpCode < 300) {
        return true;
    }
    error_log('[mailer] Brevo API send failed to ' . $toEmail . ' (HTTP ' . $httpCode . '): ' . $response);
    return false;
}

/** Wraps inner HTML in a simple branded email shell. */
function email_wrap(string $title, string $innerHtml): string {
    $site = e(SITE_NAME);
    return '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fa;padding:32px 0">'
      . '<div style="max-width:560px;margin:0 auto;background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e2e8f0">'
      . '<div style="background:#0f172a;padding:22px 28px"><span style="color:#f59e0b;font-weight:700;font-size:18px">' . $site . '</span></div>'
      . '<div style="padding:28px;color:#1e293b;font-size:15px;line-height:1.6">' . $innerHtml . '</div>'
      . '<div style="padding:18px 28px;background:#f4f6fa;color:#64748b;font-size:12.5px">This is an automated message from ' . $site . '. Please do not reply directly to this email.</div>'
      . '</div></div>';
}
