<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Segnalazione extends Model
{
    use HasFactory;

    protected $table = 'segnalazioni';

    protected $fillable = [
        'foto',
        'tipo',
        'lat',
        'lng',
        'descrizione',
        'status',
        'is_resolved',
    ];

    protected $casts = [
        'is_resolved' => 'boolean',
    ];

    public function getFotoAttribute($value)
    {
        if ($value === null) {
            return [];
        }
        if (is_string($value)) {
            return json_decode($value, true) ?? [];
        }

        return $value;
    }

    public function setFotoAttribute($value)
    {
        $this->attributes['foto'] = is_array($value) ? json_encode($value) : $value;
    }

    /**
     * Calcola la distanza in metri tra due coordinate usando la formula di Haversine.
     *
     * @param  float  $lat1  Latitudine punto 1
     * @param  float  $lng1  Longitudine punto 1
     * @param  float|null  $lat2  Latitudine punto 2 (null per usare il record corrente)
     * @param  float|null  $lng2  Longitudine punto 2 (null per usare il record corrente)
     * @return float Distanza in metri
     */
    public static function haversineDistance(
        float $lat1,
        float $lng1,
        ?float $lat2 = null,
        ?float $lng2 = null
    ): float {
        // Se non specificati, usa le coordinate del record corrente
        if ($lat2 === null || $lng2 === null) {
            $lat2 = $lat1;
            $lng2 = $lng1;
        }

        $earthRadius = 6371000; // radio della Terra in metri

        $latFrom = deg2rad($lat1);
        $lngFrom = deg2rad($lng1);
        $latTo = deg2rad($lat2);
        $lngTo = deg2rad($lng2);

        $latDelta = $latTo - $latFrom;
        $lngDelta = $lngTo - $lngFrom;

        $a = sin($latDelta / 2) ** 2
            + cos($latFrom) * cos($latTo) * sin($lngDelta / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Verifica se la segnalazione è approvata.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Relazione con l'utente che ha fatto la segnalazione (opzionale).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function utente()
    {
        return $this->belongsTo(User::class);
    }
}
