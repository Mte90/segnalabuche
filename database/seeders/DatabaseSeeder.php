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

        $admin = User::where('email', $adminEmail)->first();
        if (! $admin) {
            $admin = User::create([
                'name' => 'Admin',
                'email' => $adminEmail,
                'password' => bcrypt('admin'),
                'is_admin' => true
            ]);
        }

        // Create sample segnalazioni with various statuses
        // Placeholder images from placehold.co

        // 1. Perdita d'acqua - pending, with photo
        Segnalazione::create([
            'foto' => ['https://placehold.co/600x400/28a745/white?text=Perdita+Acqua'],
            'tipo' => 'perdita d\'acqua',
            'lat' => 42.4097,
            'lng' => 12.8607,
            'descrizione' => 'Perdita d\'acqua in via Roma, fronte civico 10',
            'status' => 'pending',
        ]);

        // 2. Buca stradale - approved, with photo
        Segnalazione::create([
            'foto' => ['https://placehold.co/600x400/dc3545/white?text=Buca+Stradale'],
            'tipo' => 'buca stradale',
            'lat' => 42.4102,
            'lng' => 12.8591,
            'descrizione' => 'Buca stradale su via Garibaldi, vicino al semaforo',
            'status' => 'approved',
        ]);

        // 3. Illuminazione pubblica - approved, with 2 photos
        Segnalazione::create([
            'foto' => [
                'https://placehold.co/600x400/ffc107/white?text=Lampione+Spento',
                'https://placehold.co/600x400/ffc107/white?text=Dettaglio+Lampione',
            ],
            'tipo' => 'illuminazione pubblica',
            'lat' => 42.4089,
            'lng' => 12.8621,
            'descrizione' => 'Lampione spento in piazza Roma',
            'status' => 'approved',
        ]);

        // 4. Tombino attappato - rejected, with photo
        Segnalazione::create([
            'foto' => ['https://placehold.co/600x400/6c757d/white?text=Tombino+Attappato'],
            'tipo' => 'tombino attappato',
            'lat' => 42.4115,
            'lng' => 12.8578,
            'descrizione' => 'Tombino attappato in via Mazzini',
            'status' => 'rejected',
        ]);

        // 5. Altro - pending, no photo
        Segnalazione::create([
            'foto' => [],
            'tipo' => 'altro',
            'lat' => 42.4093,
            'lng' => 12.8635,
            'descrizione' => 'Cartellonistica fuori uso in via Cavour',
            'status' => 'pending',
        ]);

        // 6. Perdita d'acqua - rejected, with photo
        Segnalazione::create([
            'foto' => ['https://placehold.co/600x400/28a745/white?text=Perdita+Parco'],
            'tipo' => 'perdita d\'acqua',
            'lat' => 42.4085,
            'lng' => 12.8599,
            'descrizione' => 'Perdita presso il parco comunale',
            'status' => 'rejected',
        ]);
    }
}
