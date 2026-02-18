<?php

namespace Database\Seeders;

use App\Models\Segnalazione;
use Illuminate\Database\Seeder;

class TestSegnalazioneSeeder extends Seeder
{
    public function run(): void
    {
        Segnalazione::truncate();

        $segnalazioni = [
            [
                'tipo' => "perdita d'acqua",
                'lat' => 42.4097,
                'lng' => 12.8607,
                'descrizione' => 'Prova perdita pendente 1',
                'status' => 'pending',
                'foto' => [],
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'tipo' => 'buca stradale',
                'lat' => 42.4100,
                'lng' => 12.8610,
                'descrizione' => 'Prova buca pendente 2',
                'status' => 'pending',
                'foto' => [],
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ],
            [
                'tipo' => 'tombino attappato',
                'lat' => 42.4095,
                'lng' => 12.8605,
                'descrizione' => 'Prova tombino approvato 1',
                'status' => 'approved',
                'foto' => [],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tipo' => 'illuminazione pubblica',
                'lat' => 42.4093,
                'lng' => 12.8635,
                'descrizione' => 'Prova illuminazione approvata 2',
                'status' => 'approved',
                'foto' => [],
                'created_at' => now()->subHours(2),
                'updated_at' => now()->subHours(2),
            ],
            [
                'tipo' => 'altro',
                'lat' => 42.4115,
                'lng' => 12.8578,
                'descrizione' => 'Prova altro rifiutato 1',
                'status' => 'rejected',
                'foto' => [],
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
            ],
        ];

        foreach ($segnalazioni as $segnalazione) {
            Segnalazione::create($segnalazione);
        }
    }
}
