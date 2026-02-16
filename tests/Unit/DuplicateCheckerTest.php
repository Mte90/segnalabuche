<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Segnalazione;
use App\Services\DuplicateChecker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DuplicateCheckerTest extends TestCase
{
    use RefreshDatabase;

    protected $duplicateChecker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->duplicateChecker = new DuplicateChecker();
    }

    public function test_calcolo_distanza_haversine_zero_metri(): void
    {
        $lat1 = 42.4097;
        $lng1 = 12.8607;
        $lat2 = 42.4097;
        $lng2 = 12.8607;

        $distanza = Segnalazione::haversineDistance($lat1, $lng1, $lat2, $lng2);

        $this->assertLessThan(0.01, $distanza);
    }

    public function test_calcolo_distanza_haversine_metri(): void
    {
        $lat1 = 42.4097;
        $lng1 = 12.8607;
        $lat2 = 42.4098;
        $lng2 = 12.8607;

        $distanza = Segnalazione::haversineDistance($lat1, $lng1, $lat2, $lng2);

        $this->assertGreaterThan(0, $distanza);
        $this->assertLessThan(20, $distanza);
    }

    public function test_calcolo_distanza_haversine_chilometri(): void
    {
        $lat1 = 42.4097;
        $lng1 = 12.8607;
        $lat2 = 42.5000;
        $lng2 = 12.8607;

        $distanza = Segnalazione::haversineDistance($lat1, $lng1, $lat2, $lng2);

        $this->assertGreaterThan(10000, $distanza);
        $this->assertLessThan(11000, $distanza);
    }

    public function test_duplicato_entro_100_metri(): void
    {
        $segnalazione1 = Segnalazione::create([
            'foto' => ['foto1.jpg'],
            'tipo' => 'perdita d\'acqua',
            'lat' => 42.4097,
            'lng' => 12.8607,
            'descrizione' => 'Prima segnalazione',
            'status' => 'approved',
            'is_resolved' => false,
        ]);

        $segnalazione2 = Segnalazione::create([
            'foto' => ['foto2.jpg'],
            'tipo' => 'perdita d\'acqua',
            'lat' => 42.40975,
            'lng' => 12.86075,
            'descrizione' => 'Seconda segnalazione vicina',
            'status' => 'pending',
            'is_resolved' => false,
        ]);

        $duplicateChecker = new DuplicateChecker();
        $duplicates = $duplicateChecker->check('perdita d\'acqua', 42.40975, 12.86075, 100);

        $this->assertNotEmpty($duplicates);
        $this->assertContains($segnalazione2->id, array_column($duplicates, 'id'));
    }

    public function test_non_duplicato_oltre_100_metri(): void
    {
        $segnalazione1 = Segnalazione::create([
            'foto' => ['foto1.jpg'],
            'tipo' => 'buca stradale',
            'lat' => 42.4097,
            'lng' => 12.8607,
            'descrizione' => 'Prima segnalazione',
            'status' => 'approved',
            'is_resolved' => false,
        ]);

        $latFuoriRaggio = 42.4200;
        $lngFuoriRaggio = 12.8700;

        $distanza = Segnalazione::haversineDistance($segnalazione1->lat, $segnalazione1->lng, $latFuoriRaggio, $lngFuoriRaggio);

        $this->assertGreaterThan(100, $distanza, "Distanza should be > 100m, got: $distanza");

        $duplicateChecker = new DuplicateChecker();
        $duplicates = $duplicateChecker->check('buca stradale', $latFuoriRaggio, $lngFuoriRaggio, 100);

        $this->assertEmpty($duplicates);
    }

    public function test_duplicato_esclude_rifiutate(): void
    {
        $segnalazioneRifiutata = Segnalazione::create([
            'foto' => ['foto1.jpg'],
            'tipo' => 'tombino attappato',
            'lat' => 42.4097,
            'lng' => 12.8607,
            'descrizione' => 'Prima segnalazione',
            'status' => 'rejected',
            'is_resolved' => false,
        ]);

        $latNuova = 42.40975;
        $lngNuova = 12.86075;

        $distanza = Segnalazione::haversineDistance($segnalazioneRifiutata->lat, $segnalazioneRifiutata->lng, $latNuova, $lngNuova);
        $this->assertLessThan(100, $distanza, "Distanza should be < 100m, got: $distanza");

        $duplicateChecker = new DuplicateChecker();
        $duplicates = $duplicateChecker->check('tombino attappato', $latNuova, $lngNuova, 100);

        $this->assertEmpty($duplicates);
    }

    public function test_duplicato_esclude_tipologie_diverse(): void
    {
        $segnalazioneAcqua = Segnalazione::create([
            'foto' => ['foto1.jpg'],
            'tipo' => 'perdita d\'acqua',
            'lat' => 42.4097,
            'lng' => 12.8607,
            'descrizione' => 'Perdita d\'acqua',
            'status' => 'approved',
            'is_resolved' => false,
        ]);

        $latBuca = 42.40975;
        $lngBuca = 12.86075;

        $distanza = Segnalazione::haversineDistance($segnalazioneAcqua->lat, $segnalazioneAcqua->lng, $latBuca, $lngBuca);
        $this->assertLessThan(100, $distanza, "Distanza should be < 100m, got: $distanza");

        $duplicateChecker = new DuplicateChecker();
        $duplicates = $duplicateChecker->check('buca stradale', $latBuca, $lngBuca, 100);

        $this->assertEmpty($duplicates);
    }
}
