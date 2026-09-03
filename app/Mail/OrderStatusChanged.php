<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusChanged extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order, public string $headline, public string $body)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->headline . ' — ' . $this->order->order_number,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.order-status');
    }
}
