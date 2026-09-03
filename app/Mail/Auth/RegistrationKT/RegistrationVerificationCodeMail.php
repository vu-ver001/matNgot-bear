<?php

namespace App\Mail\Auth\RegistrationKT;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegistrationVerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly string $code) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Mã xác nhận đăng ký Mật Ngọt Bear',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.auth.registrationKT.verification-code',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
