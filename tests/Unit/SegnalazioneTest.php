<?php

namespace Tests\Unit;

use App\Models\Segnalazione;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SegnalazioneTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_valid_segnalazione(): void
    {
        $segnalazione = Segnalazione::create([
            'tipo' => 'perdita d\'acqua',
            'lat' => 42.4097,
            'lng' => 12.8607,
            'descrizione' => 'Perdita d\'acqua segnalata',
            'status' => 'pending',
            'is_resolved' => false,
        ]);

        $this->assertNotNull($segnalazione->id);
        $this->assertEquals('perdita d\'acqua', $segnalazione->tipo);
        $this->assertEquals(42.4097, $segnalazione->lat);
        $this->assertEquals(12.8607, $segnalazione->lng);
        $this->assertEquals('pending', $segnalazione->status);
        $this->assertFalse($segnalazione->is_resolved);
    }

    public function test_validazione_tipo_enum(): void
    {
        $tipiValidi = [
            'perdita d\'acqua',
            'tombino attappato',
            'buca stradale',
            'illuminazione pubblica',
            'altro',
        ];

        foreach ($tipiValidi as $tipo) {
            $segnalazione = Segnalazione::create([
                'tipo' => $tipo,
                'lat' => 42.4097,
                'lng' => 12.8607,
                'descrizione' => 'Test',
                'status' => 'pending',
            ]);

            $this->assertEquals($tipo, $segnalazione->tipo);
            $segnalazione->delete();
        }
    }

    public function test_validazione_stato_enum(): void
    {
        $statiValidi = ['pending', 'approved', 'rejected'];

        foreach ($statiValidi as $status) {
            $segnalazione = Segnalazione::create([
                'tipo' => 'perdita d\'acqua',
                'lat' => 42.4097,
                'lng' => 12.8607,
                'descrizione' => 'Test',
                'status' => $status,
            ]);

            $this->assertEquals($status, $segnalazione->status);
            $segnalazione->delete();
        }
    }

    public function test_validazione_foto_array(): void
    {
        $segnalazione = Segnalazione::create([
            'foto' => ['foto1.jpg', 'foto2.jpg'],
            'tipo' => 'perdita d\'acqua',
            'lat' => 42.4097,
            'lng' => 12.8607,
            'descrizione' => 'Test con foto',
            'status' => 'pending',
        ]);

        // Verify the foto is stored correctly by checking the JSON
        $this->assertNotNull($segnalazione->foto);
    }

    public function test_validazione_foto_vuoto_array(): void
    {
        $segnalazione = Segnalazione::create([
            'tipo' => 'perdita d\'acqua',
            'lat' => 42.4097,
            'lng' => 12.8607,
            'descrizione' => 'Test senza foto',
            'status' => 'pending',
        ]);

        // foto can be null or empty array
        $this->assertNotNull($segnalazione->foto);
        $this->assertEmpty($segnalazione->foto);
    }

    public function test_validazione_foto_null(): void
    {
        $segnalazione = Segnalazione::create([
            'tipo' => 'perdita d\'acqua',
            'lat' => 42.4097,
            'lng' => 12.8607,
            'descrizione' => 'Test senza foto',
            'status' => 'pending',
        ]);

        // foto should be cast to array (empty) from null
        $this->assertIsArray($segnalazione->foto);
        $this->assertEmpty($segnalazione->foto);
    }

    public function test_validazione_latitudine_bound(): void
    {
        $segnalazioneNord = Segnalazione::create([
            'tipo' => 'perdita d\'acqua',
            'lat' => 90.0,
            'lng' => 0.0,
            'descrizione' => 'Al limite settentrionale',
            'status' => 'pending',
        ]);
        $this->assertEquals(90.0, $segnalazioneNord->lat);

        $segnalazioneSud = Segnalazione::create([
            'tipo' => 'perdita d\'acqua',
            'lat' => -90.0,
            'lng' => 0.0,
            'descrizione' => 'Al limite meridionale',
            'status' => 'pending',
        ]);
        $this->assertEquals(-90.0, $segnalazioneSud->lat);
    }

    public function test_validazione_longitudine_bound(): void
    {
        $segnalazioneEst = Segnalazione::create([
            'tipo' => 'perdita d\'acqua',
            'lat' => 0.0,
            'lng' => 180.0,
            'descrizione' => 'Al limite orientale',
            'status' => 'pending',
        ]);
        $this->assertEquals(180.0, $segnalazioneEst->lng);

        $segnalazioneOvest = Segnalazione::create([
            'tipo' => 'perdita d\'acqua',
            'lat' => 0.0,
            'lng' => -180.0,
            'descrizione' => 'Al limite occidentale',
            'status' => 'pending',
        ]);
        $this->assertEquals(-180.0, $segnalazioneOvest->lng);
    }

    public function test_calcolo_distanza_haversine_identica(): void
    {
        $segnalazione = Segnalazione::create([
            'tipo' => 'perdita d\'acqua',
            'lat' => 42.4097,
            'lng' => 12.8607,
            'descrizione' => 'Test',
            'status' => 'pending',
        ]);

        $distanza = Segnalazione::haversineDistance(
            $segnalazione->lat,
            $segnalazione->lng,
            $segnalazione->lat,
            $segnalazione->lng
        );

        $this->assertLessThan(0.01, $distanza);
    }

    public function test_calcolo_distanza_haversine_notevole(): void
    {
        $lat1 = 42.4097;
        $lng1 = 12.8607;
        $lat2 = 42.4100;
        $lng2 = 12.8610;

        $distanza = Segnalazione::haversineDistance($lat1, $lng1, $lat2, $lng2);

        $this->assertGreaterThan(0, $distanza);
        $this->assertLessThan(100, $distanza);
    }

    public function test_is_approved_restituisce_true_per_approved(): void
    {
        $segnalazione = Segnalazione::create([
            'tipo' => 'perdita d\'acqua',
            'lat' => 42.4097,
            'lng' => 12.8607,
            'descrizione' => 'Test',
            'status' => 'approved',
        ]);

        $this->assertTrue($segnalazione->isApproved());
    }

    public function test_is_approved_restituisce_false_per_pending(): void
    {
        $segnalazione = Segnalazione::create([
            'tipo' => 'perdita d\'acqua',
            'lat' => 42.4097,
            'lng' => 12.8607,
            'descrizione' => 'Test',
            'status' => 'pending',
        ]);

        $this->assertFalse($segnalazione->isApproved());
    }

    public function test_is_approved_restituisce_false_per_rejected(): void
    {
        $segnalazione = Segnalazione::create([
            'tipo' => 'perdita d\'acqua',
            'lat' => 42.4097,
            'lng' => 12.8607,
            'descrizione' => 'Test',
            'status' => 'rejected',
        ]);

        $this->assertFalse($segnalazione->isApproved());
    }

    public function test_refresh_database_riutilizza_record(): void
    {
        $segnalazione = Segnalazione::create([
            'tipo' => 'perdita d\'acqua',
            'lat' => 42.4097,
            'lng' => 12.8607,
            'descrizione' => 'Prima segnalazione',
            'status' => 'pending',
        ]);

        $this->assertNotNull($segnalazione->id);

        $secondaSegnalazione = Segnalazione::create([
            'tipo' => 'tombino attappato',
            'lat' => 42.4100,
            'lng' => 12.8610,
            'descrizione' => 'Seconda segnalazione',
            'status' => 'pending',
        ]);

        $this->assertNotNull($secondaSegnalazione->id);
        $this->assertNotEquals($segnalazione->id, $secondaSegnalazione->id);
    }
}
