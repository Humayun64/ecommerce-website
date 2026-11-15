<?php

namespace App\Mail\Transport;

use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;

/**
 * Sends through PHP's own mail() function, so the shop can email without a
 * mailbox password. Shared hosts such as Hostinger switch off proc_open,
 * which Laravel's "sendmail" mailer needs; mail() still works there.
 *
 * The message is built and encoded by Symfony first, so nothing typed by a
 * customer can add its own headers.
 */
class PhpMailTransport extends AbstractTransport
{
    protected function doSend(SentMessage $message): void
    {
        if (! function_exists('mail')) {
            throw new TransportException('This server has PHP mail() switched off. Use an SMTP mailbox instead.');
        }

        $raw = $message->toString();
        $cut = strpos($raw, "\r\n\r\n");

        if ($cut === false) {
            throw new TransportException('The email could not be prepared for sending.');
        }

        $body = substr($raw, $cut + 4);

        // Headers can be folded over several lines; a line starting with a
        // space or tab belongs to the header above it.
        $headers = preg_split('/\r\n(?![ \t])/', substr($raw, 0, $cut));

        $to      = '';
        $subject = '';
        $keep    = [];

        foreach ($headers as $header) {
            if (stripos($header, 'To:') === 0) {
                $to = trim(substr($header, 3));
            } elseif (stripos($header, 'Subject:') === 0) {
                $subject = trim(substr($header, 8));
            } elseif (stripos($header, 'Bcc:') !== 0) {
                $keep[] = $header;
            }
        }

        // mail() takes these two on one line each.
        $to      = preg_replace('/\r\n[ \t]+/', ' ', $to);
        $subject = preg_replace('/\r\n[ \t]+/', ' ', $subject);

        if ($to === '') {
            throw new TransportException('The email has nobody to go to.');
        }

        // The bounce address. Only passed on when it is a plain address, as
        // it becomes part of the command the server runs.
        $sender = $message->getEnvelope()->getSender()->getAddress();
        $extra  = preg_match('/^[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}$/', $sender) ? '-f' . $sender : '';

        $sent = $extra !== ''
            ? @mail($to, $subject, $body, implode("\r\n", $keep), $extra)
            : @mail($to, $subject, $body, implode("\r\n", $keep));

        if (! $sent) {
            throw new TransportException('The web server did not accept the email. An SMTP mailbox is the reliable alternative.');
        }
    }

    public function __toString(): string
    {
        return 'phpmail://default';
    }
}
