<?php

namespace App\Mail;

use App\Models\Segnalazione;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApprovedNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Segnalazione $segnalazione;

    public ?string $customReplyTo = null;

    public function __construct(Segnalazione $segnalazione)
    {
        $this->segnalazione = $segnalazione;
    }

    public function envelope(): Envelope
    {
        $replyTo = $this->customReplyTo ?? config('mail.from.address');

        return new Envelope(
            subject: 'Segnalazione approvata',
            replyTo: [$replyTo],
        );
    }

    public function withReplyTo(string $replyTo): self
    {
        $this->customReplyTo = $replyTo;

        return $this;
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.approved_notification',
        );
    }

    public function build()
    {
        return $this->view('mail.approved_notification')
            ->with(['segnalazione' => $this->segnalazione]);
    }
}
