<?php

namespace Tests\Feature;

use App\Models\Segnalazione;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSegnalazioneTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@comune.bugliano.it',
        ]);
    }

    public function test_admin_edit_endpoint_returns_200_on_success(): void
    {
        $segnalazione = Segnalazione::create([
            'foto' => ['foto1.jpg'],
            'tipo' => 'perdita d\'acqua',
            'lat' => 42.4097,
            'lng' => 12.8607,
            'descrizione' => 'Perdita d\'acqua segnalata',
            'status' => 'pending',
            'is_resolved' => false,
        ]);

        $response = $this->actingAs($this->adminUser)->putJson(
            "/admin/segnalazioni/{$segnalazione->id}",
            [
                'tipo' => 'tombino attappato',
                'status' => 'approved',
                'descrizione' => 'Perdita d\'acqua aggiornata',
                'lat' => 42.4100,
                'lng' => 12.8610,
            ]
        );

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'segnalazione',
        ]);
        $this->assertEquals('Segnalazione aggiornata con successo', $response->json('message'));
        $this->assertEquals('tombino attappato', $response->json('segnalazione.tipo'));
        $this->assertEquals('approved', $response->json('segnalazione.status'));
    }

    public function test_admin_edit_endpoint_validation_errors_return_422(): void
    {
        $segnalazione = Segnalazione::create([
            'foto' => ['foto1.jpg'],
            'tipo' => 'perdita d\'acqua',
            'lat' => 42.4097,
            'lng' => 12.8607,
            'descrizione' => 'Perdita d\'acqua segnalata',
            'status' => 'pending',
            'is_resolved' => false,
        ]);

        // Test missing required tipo
        $response = $this->actingAs($this->adminUser)->putJson(
            "/admin/segnalazioni/{$segnalazione->id}",
            [
                'status' => 'approved',
                'descrizione' => 'Perdita d\'acqua aggiornata',
                'lat' => 42.4100,
                'lng' => 12.8610,
            ]
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('tipo');

        // Test invalid tipo
        $response = $this->actingAs($this->adminUser)->putJson(
            "/admin/segnalazioni/{$segnalazione->id}",
            [
                'tipo' => 'tipo_invalido',
                'status' => 'approved',
                'descrizione' => 'Perdita d\'acqua aggiornata',
                'lat' => 42.4100,
                'lng' => 12.8610,
            ]
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('tipo');

        // Test invalid lat
        $response = $this->actingAs($this->adminUser)->putJson(
            "/admin/segnalazioni/{$segnalazione->id}",
            [
                'tipo' => 'perdita d\'acqua',
                'status' => 'approved',
                'descrizione' => 'Perdita d\'acqua aggiornata',
                'lat' => 100.0,
                'lng' => 12.8610,
            ]
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('lat');

        // Test invalid lng
        $response = $this->actingAs($this->adminUser)->putJson(
            "/admin/segnalazioni/{$segnalazione->id}",
            [
                'tipo' => 'perdita d\'acqua',
                'status' => 'approved',
                'descrizione' => 'Perdita d\'acqua aggiornata',
                'lat' => 42.4100,
                'lng' => 200.0,
            ]
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('lng');
    }

    public function test_admin_edit_endpoint_non_admin_user_receives_403(): void
    {
        $segnalazione = Segnalazione::create([
            'foto' => ['foto1.jpg'],
            'tipo' => 'perdita d\'acqua',
            'lat' => 42.4097,
            'lng' => 12.8607,
            'descrizione' => 'Perdita d\'acqua segnalata',
            'status' => 'pending',
            'is_resolved' => false,
        ]);

        $nonAdminUser = User::factory()->create([
            'name' => '普通 User',
            'email' => 'user@example.com',
        ]);

        $response = $this->actingAs($nonAdminUser)->putJson(
            "/admin/segnalazioni/{$segnalazione->id}",
            [
                'tipo' => 'tombino attappato',
                'status' => 'approved',
            ]
        );

        $response->assertStatus(403);
    }

    public function test_admin_edit_endpoint_segnalazione_not_found_returns_404(): void
    {
        $response = $this->actingAs($this->adminUser)->putJson(
            '/admin/segnalazioni/99999',
            [
                'tipo' => 'tombino attappato',
                'status' => 'approved',
            ]
        );

        $response->assertStatus(404);
        $response->assertJson([
            'error' => 'Segnalazione non trovata',
        ]);
    }

    public function test_admin_edit_endpoint_descrizione_max_length_validation(): void
    {
        $segnalazione = Segnalazione::create([
            'foto' => ['foto1.jpg'],
            'tipo' => 'perdita d\'acqua',
            'lat' => 42.4097,
            'lng' => 12.8607,
            'descrizione' => 'Perdita d\'acqua segnalata',
            'status' => 'pending',
            'is_resolved' => false,
        ]);

        $longDescription = str_repeat('a', 1001);

        $response = $this->actingAs($this->adminUser)->putJson(
            "/admin/segnalazioni/{$segnalazione->id}",
            [
                'tipo' => 'perdita d\'acqua',
                'status' => 'pending',
                'descrizione' => $longDescription,
                'lat' => 42.4097,
                'lng' => 12.8607,
            ]
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('descrizione');
    }
}
