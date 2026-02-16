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

    public function __construct(Segnalazione $segnalazione)
    {
        $this->segnalazione = $segnalazione;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Segnalazione approvata',
        );
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
