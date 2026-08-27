<?php

namespace App\Mail\Auth\PasswordResetKT;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetVerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly string $code) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Mã xác nhận đặt lại mật khẩu Mật Ngọt Bear',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.auth.passwordResetKT.verification-code',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
