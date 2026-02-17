<?php

namespace App\Mail;

use App\Models\Segnalazione;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApprovalRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public Segnalazione $segnalazione;

    public array $duplicates;

    public ?string $customReplyTo = null;

    public function __construct(Segnalazione $segnalazione, array $duplicates)
    {
        $this->segnalazione = $segnalazione;
        $this->duplicates = $duplicates;
    }

    public function envelope(): Envelope
    {
        $replyTo = $this->customReplyTo ?? config('mail.from.address');

        return new Envelope(
            subject: 'Nuova segnalazione da approvare',
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
            view: 'mail.approval_request',
        );
    }

    public function build()
    {
        return $this->view('mail.approval_request')
            ->with([
                'segnalazione' => $this->segnalazione,
                'duplicates' => $this->duplicates,
            ]);
    }
}
