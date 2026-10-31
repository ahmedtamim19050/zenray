<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EnquiryReceived extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<string, string|null>  $enquiry  Validated enquiry fields.
     */
    public function __construct(public array $enquiry)
    {
        //
    }

    public function envelope(): Envelope
    {
        $type = ($this->enquiry['type'] ?? null) === 'quote' ? 'Quote request' : 'Website enquiry';

        return new Envelope(
            subject: "{$type}: {$this->enquiry['interest_label']} ({$this->enquiry['name']})",
            replyTo: filled($this->enquiry['email'] ?? null)
                ? [new Address($this->enquiry['email'], $this->enquiry['name'])]
                : [],
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'mail.enquiry-received',
        );
    }
}
