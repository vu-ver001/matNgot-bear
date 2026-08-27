<?php

namespace App\Mail\ProfileKT;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailChangeCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly string $code) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Mã xác nhận đổi email Mật Ngọt Bear',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.profileKT.email-change-code',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
