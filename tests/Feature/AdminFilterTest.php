<?php

namespace Tests\Feature;

use App\Models\Segnalazione;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_segnalazioni_endpoint_returns_200(): void
    {
        $response = $this->getJson('/admin/segnalazioni?status=all&display=list');

        $response->assertStatus(200);
    }

    public function test_admin_segnalazioni_filter_by_status(): void
    {
        Segnalazione::create([
            'tipo' => 'perdita d\'acqua',
            'lat' => 42.4097,
            'lng' => 12.8607,
            'status' => 'pending',
        ]);
        Segnalazione::create([
            'tipo' => 'tombino attappato',
            'lat' => 42.4100,
            'lng' => 12.8610,
            'status' => 'approved',
        ]);
        Segnalazione::create([
            'tipo' => 'buca stradale',
            'lat' => 42.4095,
            'lng' => 12.8605,
            'status' => 'rejected',
        ]);

        $response = $this->getJson('/admin/segnalazioni?status=pending&display=list');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertNotNull($data);

        $pendingCount = count(array_filter($data ?? [], fn ($seg) => $seg['status'] === 'pending'));
        $this->assertGreaterThan(0, $pendingCount);
    }

    public function test_admin_segnalazioni_filter_by_tipo(): void
    {
        Segnalazione::create([
            'tipo' => 'perdita d\'acqua',
            'lat' => 42.4097,
            'lng' => 12.8607,
            'status' => 'pending',
        ]);
        Segnalazione::create([
            'tipo' => 'perdita d\'acqua',
            'lat' => 42.4100,
            'lng' => 12.8610,
            'status' => 'pending',
        ]);
        Segnalazione::create([
            'tipo' => 'tombino attappato',
            'lat' => 42.4095,
            'lng' => 12.8605,
            'status' => 'pending',
        ]);

        $response = $this->getJson('/admin/segnalazioni?tipo=perdita+d%27acqua&display=list');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertNotNull($data);

        $perditaAcquaCount = count(array_filter($data ?? [], fn ($seg) => $seg['tipo'] === 'perdita d\'acqua'));
        $this->assertEquals(2, $perditaAcquaCount);
    }

    public function test_admin_segnalazioni_ajax_response_structure(): void
    {
        Segnalazione::create([
            'tipo' => 'perdita d\'acqua',
            'lat' => 42.4097,
            'lng' => 12.8607,
            'status' => 'pending',
        ]);

        $response = $this->getJson('/admin/segnalazioni?status=pending&display=list&ajax=true');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'html',
            'count',
            'stats' => [
                'total',
                'pending',
                'approved',
                'rejected',
            ],
        ]);
    }

    public function test_admin_segnalazioni_stats_counts(): void
    {
        Segnalazione::create([
            'tipo' => 'perdita d\'acqua',
            'lat' => 42.4097,
            'lng' => 12.8607,
            'status' => 'pending',
        ]);
        Segnalazione::create([
            'tipo' => 'tombino attappato',
            'lat' => 42.4100,
            'lng' => 12.8610,
            'status' => 'approved',
        ]);
        Segnalazione::create([
            'tipo' => 'buca stradale',
            'lat' => 42.4095,
            'lng' => 12.8605,
            'status' => 'rejected',
        ]);
        Segnalazione::create([
            'tipo' => 'illuminazione pubblica',
            'lat' => 42.4098,
            'lng' => 12.8608,
            'status' => 'pending',
        ]);

        $response = $this->getJson('/admin/segnalazioni?status=all&display=list&ajax=true');

        $response->assertStatus(200);
        $stats = $response->json('stats');

        $this->assertEquals(4, $stats['total']);
        $this->assertEquals(2, $stats['pending']);
        $this->assertEquals(1, $stats['approved']);
        $this->assertEquals(1, $stats['rejected']);
    }

    public function test_admin_segnalazioni_export_endpoint(): void
    {
        Segnalazione::create([
            'tipo' => 'perdita d\'acqua',
            'lat' => 42.4097,
            'lng' => 12.8607,
            'status' => 'pending',
            'descrizione' => 'Test segnalazione',
        ]);

        $response = $this->get('/admin/segnalazioni/export?status=pending');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv');
        $response->assertHeader('Content-Disposition', 'attachment; filename=segnalazioni_*.csv');
    }

    public function test_admin_segnalazioni_update_status_endpoint(): void
    {
        $segnalazione = Segnalazione::create([
            'tipo' => 'perdita d\'acqua',
            'lat' => 42.4097,
            'lng' => 12.8607,
            'status' => 'pending',
        ]);

        $response = $this->putJson("/admin/segnalazioni/{$segnalazione->id}/status", [
            'status' => 'approved',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('approved', Segnalazione::find($segnalazione->id)->status);
    }

    public function test_admin_segnalazioni_update_status_validation_errors(): void
    {
        $segnalazione = Segnalazione::create([
            'tipo' => 'perdita d\'acqua',
            'lat' => 42.4097,
            'lng' => 12.8607,
            'status' => 'pending',
        ]);

        $response = $this->putJson("/admin/segnalazioni/{$segnalazione->id}/status", [
            'status' => 'invalid_status',
        ]);

        $response->assertStatus(422);
    }

    public function test_admin_segnalazioni_delete_endpoint(): void
    {
        $segnalazione = Segnalazione::create([
            'tipo' => 'perdita d\'acqua',
            'lat' => 42.4097,
            'lng' => 12.8607,
            'status' => 'pending',
        ]);

        $response = $this->deleteJson("/admin/segnalazioni/{$segnalazione->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Segnalazione eliminata con successo',
        ]);
        $this->assertNull(Segnalazione::find($segnalazione->id));
    }
}
