<?php

namespace App\Listeners;

use App\Models\EmailResponseMessage;
use App\Models\Segnalazione;
use Illuminate\Support\Facades\DB;

class ProcessEmailResponses
{
    protected $imapStream = null;

    public function handle(): void
    {
        $host = env('IMAP_HOST');
        $username = env('IMAP_USERNAME');
        $password = env('IMAP_PASSWORD');

        if (! $host || ! $username || ! $password) {
            return;
        }

        $port = env('IMAP_PORT', 993);
        $encryption = env('IMAP_ENCRYPTION', 'ssl');

        $connectionString = "{{$host}}:{$port}/{$encryption}/imap/ssl/novalidate-cert";

        $this->imapStream = @imap_open($connectionString, $username, $password);

        if (! $this->imapStream) {
            return;
        }

        $folders = imap_list($this->imapStream, $connectionString, '*');

        foreach ($folders as $folder) {
            $this->processFolder($folder);
        }

        imap_close($this->imapStream);
    }

    protected function processFolder(string $folder): void
    {
        $stream = @imap_open($folder, env('IMAP_USERNAME'), env('IMAP_PASSWORD'));

        if (! $stream) {
            return;
        }

        $emails = imap_search($stream, 'UNSEEN');

        if (! $emails) {
            imap_close($stream);

            return;
        }

        foreach ($emails as $emailId) {
            $structure = imap_fetchstructure($stream, $emailId);
            $mail = $this->parseMail($stream, $emailId, $structure);

            if ($mail) {
                $this->processIncomingEmail($mail);
                imap_setflag_full($stream, $emailId, '\\Seen');
            }
        }

        imap_close($stream);
    }

    protected function parseMail($stream, int $emailId, object $structure): ?array
    {
        $header = imap_headerinfo($stream, $emailId);
        $message = imap_fetchbody($stream, $emailId, 1);

        if (isset($structure->parts)) {
            foreach ($structure->parts as $part) {
                if ($part->subtype === 'PLAIN') {
                    $message = imap_fetchbody($stream, $emailId, 1 .'.'.array_search($part, $structure->parts) + 1);
                    break;
                }
            }
        }

        $message = imap_8bit($message);

        return [
            'id' => $emailId,
            'header' => $header,
            'message' => $message,
            'structure' => $structure,
        ];
    }

    protected function processIncomingEmail(array $mail): void
    {
        $header = $mail['header'];
        $inReplyTo = $this->extractInReplyTo($header->references ?? '');
        $externalMessageId = $header->message_id ?? null;

        if (! $externalMessageId) {
            return;
        }

        $segnalazione = $this->findSegnalazioneByReplyTo($inReplyTo);

        if (! $segnalazione) {
            return;
        }

        DB::transaction(function () use ($segnalazione, $mail, $externalMessageId, $inReplyTo) {
            EmailResponseMessage::create([
                'segnalazione_id' => $segnalazione->id,
                'type' => 'received',
                'subject' => $header->subject ?? null,
                'body' => $this->extractEmailBody($mail['message']),
                'external_message_id' => $externalMessageId,
                'in_reply_to' => $inReplyTo,
                'metadata' => [
                    'from' => $this->formatAddresses($header->from ?? []),
                    'to' => $this->formatAddresses($header->to ?? []),
                    'date' => $header->date ?? null,
                ],
                'received_at' => now(),
                'status' => 'delivered',
            ]);
        });
    }

    protected function extractInReplyTo(string $references): ?string
    {
        $refs = array_filter(explode(' ', $references));

        return end($refs) ?: null;
    }

    protected function findSegnalazioneByReplyTo(?string $inReplyTo): ?Segnalazione
    {
        if (! $inReplyTo) {
            return null;
        }

        $message = EmailResponseMessage::where('external_message_id', $inReplyTo)
            ->where('type', 'sent')
            ->first();

        return $message?->segnalazione;
    }

    protected function extractEmailBody(string $message): string
    {
        return strip_tags($message);
    }

    protected function formatAddresses(array $addresses): array
    {
        return array_map(function ($addr) {
            return $addr->mailbox.'@'.$addr->host;
        }, $addresses);
    }
}
