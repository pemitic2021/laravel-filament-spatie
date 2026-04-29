<?php 

// app/Mail/VerifyEmailWithCode.php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class VerifyEmailWithCode extends Mailable
{
    use Queueable;

    public function __construct(public string $code) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Vaš verifikacioni kod');
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.verify-code');
    }

}
