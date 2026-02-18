<?php

namespace Tests\Feature;

use App\Models\Segnalazione;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@comune.bugliano.it',
            'is_admin' => true,
        ]);
    }

    public function test_admin_segnalazioni_endpoint_returns_200(): void
    {
        $response = $this->actingAs($this->adminUser)->getJson('/admin/segnalazioni?status=all&display=list');

        $response->assertStatus(200);
    }

    public function test_admin_segnalazioni_filter_by_status(): void
    {
        Segnalazione::create([
            'tipo' => 'perdita d\'acqua',
            'lat' => 42.4097,
            'lng' => 12.8607,
            'descrizione' => 'Prima segnalazione',
            'status' => 'pending',
        ]);
        Segnalazione::create([
            'tipo' => 'tombino attappato',
            'lat' => 42.4100,
            'lng' => 12.8610,
            'descrizione' => 'Seconda segnalazione',
            'status' => 'approved',
        ]);
        Segnalazione::create([
            'tipo' => 'buca stradale',
            'lat' => 42.4095,
            'lng' => 12.8605,
            'descrizione' => 'Terza segnalazione',
            'status' => 'rejected',
        ]);

        $response = $this->actingAs($this->adminUser)->getJson('/admin/segnalazioni?status=pending&display=list&ajax=true');

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
        $this->assertEquals(1, $response->json('count'));
    }

    public function test_admin_segnalazioni_filter_by_tipo(): void
    {
        Segnalazione::create([
            'tipo' => 'perdita d\'acqua',
            'lat' => 42.4097,
            'lng' => 12.8607,
            'descrizione' => 'Prima segnalazione',
            'status' => 'pending',
        ]);
        Segnalazione::create([
            'tipo' => 'perdita d\'acqua',
            'lat' => 42.4100,
            'lng' => 12.8610,
            'descrizione' => 'Seconda segnalazione',
            'status' => 'pending',
        ]);
        Segnalazione::create([
            'tipo' => 'tombino attappato',
            'lat' => 42.4095,
            'lng' => 12.8605,
            'descrizione' => 'Terza segnalazione',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->adminUser)->getJson('/admin/segnalazioni?tipo=perdita+d%27acqua&display=list&ajax=true');

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
        $this->assertEquals(2, $response->json('count'));
    }

    public function test_admin_segnalazioni_ajax_response_structure(): void
    {
        Segnalazione::create([
            'tipo' => 'perdita d\'acqua',
            'lat' => 42.4097,
            'lng' => 12.8607,
            'descrizione' => 'Prima segnalazione',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->adminUser)->getJson('/admin/segnalazioni?status=pending&display=list&ajax=true');

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
            'descrizione' => 'Prima segnalazione',
            'status' => 'pending',
        ]);
        Segnalazione::create([
            'tipo' => 'tombino attappato',
            'lat' => 42.4100,
            'lng' => 12.8610,
            'descrizione' => 'Seconda segnalazione',
            'status' => 'approved',
        ]);
        Segnalazione::create([
            'tipo' => 'buca stradale',
            'lat' => 42.4095,
            'lng' => 12.8605,
            'descrizione' => 'Terza segnalazione',
            'status' => 'rejected',
        ]);
        Segnalazione::create([
            'tipo' => 'illuminazione pubblica',
            'lat' => 42.4098,
            'lng' => 12.8608,
            'descrizione' => 'Quarta segnalazione',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->adminUser)->getJson('/admin/segnalazioni?status=all&display=list&ajax=true');

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
            'descrizione' => 'Test segnalazione',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->adminUser)->get('/admin/segnalazioni/export?status=pending');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
        $contentDisposition = $response->headers->get('Content-Disposition');
        $this->assertStringStartsWith('attachment; filename="segnalazioni_20', $contentDisposition);
        $this->assertStringEndsWith('.csv"', $contentDisposition);
    }

    public function test_admin_segnalazioni_update_status_endpoint(): void
    {
        $segnalazione = Segnalazione::create([
            'tipo' => 'perdita d\'acqua',
            'lat' => 42.4097,
            'lng' => 12.8607,
            'descrizione' => 'Test segnalazione',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->adminUser)->putJson("/admin/segnalazioni/{$segnalazione->id}/status", [
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
            'descrizione' => 'Test segnalazione',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->adminUser)->putJson("/admin/segnalazioni/{$segnalazione->id}/status", [
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
            'descrizione' => 'Test segnalazione',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->adminUser)->deleteJson("/admin/segnalazioni/{$segnalazione->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Segnalazione eliminata con successo',
        ]);
        $this->assertNull(Segnalazione::find($segnalazione->id));
    }
}
