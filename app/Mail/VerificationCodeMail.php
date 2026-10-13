<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    /** $purpose is 'login' (default) or 'register'. */
    public function __construct(
        public string $code,
        public int $minutes = 10,
        public string $purpose = 'login',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->purpose === 'register' ? 'Verify your email to request an account' : 'Your verification code');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.verification-code');
    }
}