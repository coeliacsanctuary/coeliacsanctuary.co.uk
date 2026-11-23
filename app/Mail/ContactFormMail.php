<?php

declare(strict_types=1);

namespace App\Mail;

use App\DataObjects\ContactFormData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactFormMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(protected ContactFormData $contactFormData)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [
                new Address($this->contactFormData->email, $this->contactFormData->name),
            ],
            subject: "New Contact Form - {$this->contactFormData->subject}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-form',
            with: [
                'data' => $this->contactFormData,
            ]
        );
    }
}
