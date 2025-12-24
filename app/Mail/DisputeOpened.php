<?php

namespace App\Mail;

use App\Models\Dispute;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DisputeOpened extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Dispute $dispute
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Dispute Received - Order #{$this->dispute->order->order_number}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.disputes.opened',
            with: [
                'dispute' => $this->dispute,
                'disputeUrl' => route('disputes.show', $this->dispute->id),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
