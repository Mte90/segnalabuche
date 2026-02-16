<?php

namespace App\Services;

use App\Models\Segnalazione;

class DuplicateChecker
{
    /**
     * Verifica se esistono segnalazioni simili entro un raggio specificato.
     *
     * @param  string  $tipo  Tipo di segnalazione
     * @param  float  $lat  Latitudine della segnalazione corrente
     * @param  float  $lng  Longitudine della segnalazione corrente
     * @param  float  $raggio  Raggio in metri (default 100)
     * @return array Array di segnalazioni simili o array vuoto
     */
    public function check(string $tipo, float $lat, float $lng, float $raggio = 100): array
    {
        $segnalazioni = Segnalazione::where('tipo', $tipo)
            ->where('status', '!=', 'rejected')
            ->get()
            ->filter(function ($segnalazione) use ($lat, $lng, $raggio) {
                $distanza = Segnalazione::haversineDistance(
                    $segnalazione->lat,
                    $segnalazione->lng,
                    $lat,
                    $lng
                );

                return $distanza <= $raggio;
            })
            ->toArray();

        return $segnalazioni;
    }
}
