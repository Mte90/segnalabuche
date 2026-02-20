<?php

declare(strict_types=1);

namespace App\Helpers;

class AdminHelper
{
    public static function statusLabel(string $status): string
    {
        return match ($status) {
            '', 'all' => 'Tutte le segnalazioni',
            'pending' => 'Segnalazioni in sospeso',
            'approved' => 'Segnalazioni approvate',
            'rejected' => 'Segnalazioni rifiutate',
            default => 'Tutte le segnalazioni',
        };
    }

    public static function getStatusMapping(): array
    {
        return [
            '' => 'Tutte le segnalazioni',
            'all' => 'Tutte le segnalazioni',
            'pending' => 'Segnalazioni in sospeso',
            'approved' => 'Segnalazioni approvate',
            'rejected' => 'Segnalazioni rifiutate',
        ];
    }
}
