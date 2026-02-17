<?php

namespace Database\Seeders;

use App\Models\Segnalazione;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminEmail = 'admin@comune.bugliano.it';

        if (! User::where('email', $adminEmail)->exists()) {
            User::factory()->create([
                'name' => 'Admin',
                'email' => $adminEmail,
                'password' => bcrypt('admin'),
            ]);
        }

        // Create sample segnalazioni with various statuses
        Segnalazione::create([
            'foto' => [],
            'tipo' => 'perdita d\'acqua',
            'lat' => 42.4097,
            'lng' => 12.8607,
            'descrizione' => 'Perdita d\'acqua in via Roma, fronte civico 10',
            'status' => 'pending',
        ]);

        Segnalazione::create([
            'foto' => [],
            'tipo' => 'buca stradale',
            'lat' => 42.4102,
            'lng' => 12.8591,
            'descrizione' => 'Buca stradale su via Garibaldi, vicino al semaforo',
            'status' => 'approved',
        ]);

        Segnalazione::create([
            'foto' => [],
            'tipo' => 'illuminazione pubblica',
            'lat' => 42.4089,
            'lng' => 12.8621,
            'descrizione' => 'Lampione spento in piazza Roma',
            'status' => 'approved',
        ]);

        Segnalazione::create([
            'foto' => [],
            'tipo' => 'tombino attappato',
            'lat' => 42.4115,
            'lng' => 12.8578,
            'descrizione' => 'Tombino attappato in via Mazzini',
            'status' => 'rejected',
        ]);

        Segnalazione::create([
            'foto' => [],
            'tipo' => 'altro',
            'lat' => 42.4093,
            'lng' => 12.8635,
            'descrizione' => 'Cartellonistica fuori uso in via Cavour',
            'status' => 'pending',
        ]);

        Segnalazione::create([
            'foto' => [],
            'tipo' => 'perdita d\'acqua',
            'lat' => 42.4085,
            'lng' => 12.8599,
            'descrizione' => 'Perdita presso il parco comunale',
            'status' => 'rejected',
        ]);
    }
}
