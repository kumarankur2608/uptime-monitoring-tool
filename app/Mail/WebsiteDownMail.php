<?php

namespace App\Mail;

use App\Models\MonitoredWebsite;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WebsiteDownMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(public MonitoredWebsite $website)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('do-not-reply@example.com', config('app.name')),
            subject: "{$this->website->url} is down!",
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'emails.website-down',
        );
    }
}
