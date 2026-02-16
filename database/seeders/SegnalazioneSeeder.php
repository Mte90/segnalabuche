<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Segnalazione;

class SegnalazioneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $segnalazioni = [
            [
                'tipo' => "perdita d'acqua",
                'lat' => 42.4097,
                'lng' => 12.8607,
                'descrizione' => 'Perdita d\'acqua lungo Via Roma, in corrispondenza del civico 25. La pavimentazione è bagnata e c\'è un rigurgito.',
                'status' => 'approved',
                'is_resolved' => false,
                'foto' => [],
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'tipo' => "tombino attappato",
                'lat' => 42.4100,
                'lng' => 12.8610,
                'descrizione' => 'Tombino completamente coperto da rifiuti e terra nel parcheggio di Piazza Vatican. Attenzione al.cattivo odore.',
                'status' => 'pending',
                'is_resolved' => false,
                'foto' => [],
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ],
            [
                'tipo' => "buca stradale",
                'lat' => 42.4095,
                'lng' => 12.8605,
                'descrizione' => 'Buca stradale profonda circa 15cm su Via Cavour, tra i civici 40 e 42. Pericolosa soprattutto di notte.',
                'status' => 'approved',
                'is_resolved' => false,
                'foto' => [],
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($segnalazioni as $segnalazione) {
            Segnalazione::create($segnalazione);
        }
    }
}
