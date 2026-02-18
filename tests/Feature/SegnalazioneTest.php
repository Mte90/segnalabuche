<?php

namespace Tests\Feature;

use App\Models\Segnalazione;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SegnalazioneTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_validazione_foto_massimo_tre(): void
    {
        $segnalazione = Segnalazione::create([
            'foto' => ['foto1.jpg', 'foto2.jpg', 'foto3.jpg'],
            'tipo' => 'perdita d\'acqua',
            'lat' => 42.4097,
            'lng' => 12.8607,
            'descrizione' => 'Descrizione di test',
            'status' => 'pending',
            'is_resolved' => false,
        ]);

        $this->assertCount(3, $segnalazione->foto);
        $this->assertEquals('perdita d\'acqua', $segnalazione->tipo);
    }

    public function test_validazione_foto_formato(): void
    {
        $segnalazione = Segnalazione::create([
            'foto' => ['immagine.jpg'],
            'tipo' => 'tombino attappato',
            'lat' => 42.4100,
            'lng' => 12.8610,
            'descrizione' => 'Tombino chiuso',
            'status' => 'pending',
            'is_resolved' => false,
        ]);

        $this->assertIsArray($segnalazione->foto);
        $this->assertCount(1, $segnalazione->foto);
    }

    public function test_validazione_geolocalizzazione_latitudine_valida(): void
    {
        $segnalazione = Segnalazione::create([
            'foto' => [],
            'tipo' => 'buca stradale',
            'lat' => 42.4097,
            'lng' => 12.8607,
            'descrizione' => 'Buca pericolosa',
            'status' => 'pending',
            'is_resolved' => false,
        ]);

        $this->assertNotNull($segnalazione->lat);
        $this->assertNotNull($segnalazione->lng);
    }

    public function test_validazione_geolocalizzazione_latitudine_estrema(): void
    {
        $segnalazioneNord = Segnalazione::create([
            'foto' => [],
            'tipo' => 'altro',
            'lat' => 90.0,
            'lng' => 0.0,
            'descrizione' => 'Al limite settentrionale',
            'status' => 'pending',
            'is_resolved' => false,
        ]);

        $this->assertEquals(90.0, $segnalazioneNord->lat);
    }

    public function test_validazione_geolocalizzazione_longitudine_estrema(): void
    {
        $segnalazione = Segnalazione::create([
            'foto' => [],
            'tipo' => 'altro',
            'lat' => 0.0,
            'lng' => 180.0,
            'descrizione' => 'Al limite orientale',
            'status' => 'pending',
            'is_resolved' => false,
        ]);

        $this->assertEquals(180.0, $segnalazione->lng);
    }

    public function test_creazione_segnalazione_valida(): void
    {
        $segnalazione = Segnalazione::create([
            'foto' => ['segnalazione_123.jpg'],
            'tipo' => 'perdita d\'acqua',
            'lat' => 42.4097,
            'lng' => 12.8607,
            'descrizione' => 'Perdita d\'acqua segnalata',
            'status' => 'pending',
            'is_resolved' => false,
        ]);

        $this->assertNotNull($segnalazione->id);
        $this->assertEquals('pending', $segnalazione->status);
        $this->assertFalse($segnalazione->is_resolved);
    }

    public function test_is_approved_segna_approvata(): void
    {
        $segnalazione = Segnalazione::create([
            'foto' => [],
            'tipo' => 'illuminazione pubblica',
            'lat' => 42.4097,
            'lng' => 12.8607,
            'descrizione' => 'Lampada rotta',
            'status' => 'approved',
            'is_resolved' => false,
        ]);

        $this->assertTrue($segnalazione->isApproved());
    }

    public function test_is_approved_segna_non_approvata(): void
    {
        $segnalazione = Segnalazione::create([
            'foto' => [],
            'tipo' => 'illuminazione pubblica',
            'lat' => 42.4097,
            'lng' => 12.8607,
            'descrizione' => 'Lampada rotta',
            'status' => 'pending',
            'is_resolved' => false,
        ]);

        $this->assertFalse($segnalazione->isApproved());
    }

    public function test_duplicato_entro_100_metri(): void
    {
        Storage::fake('public');

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

        $distanza = Segnalazione::haversineDistance(
            $segnalazione1->lat,
            $segnalazione1->lng,
            $segnalazione2->lat,
            $segnalazione2->lng
        );

        $this->assertLessThan(100, $distanza);
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

        $segnalazione2 = Segnalazione::create([
            'foto' => ['foto2.jpg'],
            'tipo' => 'buca stradale',
            'lat' => 42.4150,
            'lng' => 12.8650,
            'descrizione' => 'Seconda segnalazione lontana',
            'status' => 'pending',
            'is_resolved' => false,
        ]);

        $distanza = Segnalazione::haversineDistance(
            $segnalazione1->lat,
            $segnalazione1->lng,
            $segnalazione2->lat,
            $segnalazione2->lng
        );

        $this->assertGreaterThan(100, $distanza);
    }

    public function test_calcolo_distanza_haversine_presizione(): void
    {
        $lat1 = 42.4097;
        $lng1 = 12.8607;
        $lat2 = 42.4097;
        $lng2 = 12.8607;

        $distanza = Segnalazione::haversineDistance($lat1, $lng1, $lat2, $lng2);

        $this->assertLessThan(0.01, $distanza);
    }

    public function test_rifiuta_segnalazione(): void
    {
        $segnalazione = Segnalazione::create([
            'foto' => ['foto1.jpg'],
            'tipo' => 'altro',
            'lat' => 42.4097,
            'lng' => 12.8607,
            'descrizione' => 'Segnalazione da rifiutare',
            'status' => 'rejected',
            'is_resolved' => false,
        ]);

        $this->assertEquals('rejected', $segnalazione->status);
        $this->assertFalse($segnalazione->isApproved());
    }
}
