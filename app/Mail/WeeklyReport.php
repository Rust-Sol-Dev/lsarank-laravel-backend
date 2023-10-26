<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class WeeklyReport extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var string
     */
    public $reportPath;

    /**
     * WeeklyReport constructor.
     * @param string $pdfReportPath
     */
    public function __construct(string $pdfReportPath)
    {
        $this->reportPath = $pdfReportPath;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Weekly Report',
            from: new Address('curtis@askfortransparency.com', 'Curtis Boyd'),
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'emails.report',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [
            Attachment::fromPath($this->reportPath)
        ];
    }
}
