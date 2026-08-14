<?php

namespace App\Mail;

use App\Models\Institution;
use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BulletinMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Institution $institution,
        public CarbonInterface $periodStart,
        public CarbonInterface $periodEnd,
        public string $pdfPath,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Boletín de :institution', ['institution' => $this->institution->name]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.bulletin',
            with: [
                'institution' => $this->institution,
                'periodStart' => $this->periodStart,
                'periodEnd' => $this->periodEnd,
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromStorageDisk('s3', $this->pdfPath)->as('boletin.pdf'),
        ];
    }
}
