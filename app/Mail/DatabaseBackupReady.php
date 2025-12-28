<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DatabaseBackupReady extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $downloadUrl,
        public string $filename,
        public string $fileSize,
        public string $expiresAt
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'AccMarket Daily Database Backup - ' . now()->format('Y-m-d'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.backup.database-ready',
            with: [
                'downloadUrl' => $this->downloadUrl,
                'filename' => $this->filename,
                'fileSize' => $this->fileSize,
                'expiresAt' => $this->expiresAt,
                'createdAt' => now()->format('F j, Y \a\t g:i A'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
