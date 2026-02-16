<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Segnalazione;
use App\Services\DuplicateChecker;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SegnalazioneControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        
        $this->withoutMiddleware(\App\Http\Middleware\RateLimitRequests::class);
    }

    public function test_endpoint_store_creazione_segnalazione_valida(): void
    {
        $response = $this->postJson('/api/segnalazioni', [
            'tipo' => 'perdita d\'acqua',
            'lat' => 42.4097,
            'lng' => 12.8607,
            'descrizione' => 'Test di creazione segnalazione',
            'foto' => [],
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'message',
            'id',
            'duplicates',
        ]);
        $this->assertEquals('Segnalazione inviata con successo', $response->json('message'));
        $this->assertNotNull($response->json('id'));
    }

    public function test_endpoint_store_validazione_foto_massimo_tre(): void
    {
        $this->markTestSkipped('GD extension non installata, non possibile testare validazione foto');
    }

    public function test_endpoint_store_validazione_foto_formato(): void
    {
        $this->markTestSkipped('GD extension non installata, non possibile testare validazione foto');
    }

    public function test_endpoint_store_validazione_foto_dimensione(): void
    {
        $this->markTestSkipped('GD extension non installata, non possibile testare validazione foto');
    }

    public function test_endpoint_store_validazione_geolocalizzazione(): void
    {
        $response = $this->postJson('/api/segnalazioni', [
            'tipo' => 'perdita d\'acqua',
            'lat' => 100.0,
            'lng' => 12.8607,
            'descrizione' => 'Test con latitudine invalida',
            'foto' => [],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('lat');
    }

    public function test_endpoint_store_validazione_tipo(): void
    {
        $response = $this->postJson('/api/segnalazioni', [
            'tipo' => 'tipo_non_valido',
            'lat' => 42.4097,
            'lng' => 12.8607,
            'descrizione' => 'Test con tipo invalido',
            'foto' => [],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('tipo');
    }

    public function test_endpoint_store_rimuove_email_honeypot(): void
    {
        $response = $this->postJson('/api/segnalazioni', [
            'tipo' => 'perdita d\'acqua',
            'lat' => 42.4097,
            'lng' => 12.8607,
            'descrizione' => 'Test honeypot',
            'website' => 'http://spam.com',
            'foto' => [],
        ]);

        $response->assertStatus(403);
    }

    public function test_endpoint_listottieni_tutte_le_segnalazioni(): void
    {
        Segnalazione::create([
            'foto' => [],
            'tipo' => 'perdita d\'acqua',
            'lat' => 42.4097,
            'lng' => 12.8607,
            'descrizione' => 'Prima segnalazione',
            'status' => 'pending',
            'is_resolved' => false,
        ]);

        Segnalazione::create([
            'foto' => [],
            'tipo' => 'tombino attappato',
            'lat' => 42.4100,
            'lng' => 12.8610,
            'descrizione' => 'Seconda segnalazione',
            'status' => 'pending',
            'is_resolved' => false,
        ]);

        Segnalazione::create([
            'foto' => [],
            'tipo' => 'buca stradale',
            'lat' => 42.4095,
            'lng' => 12.8605,
            'descrizione' => 'Terza segnalazione',
            'status' => 'pending',
            'is_resolved' => false,
        ]);

        $response = $this->getJson('/api/segnalazioni');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(3, $data);
    }

    public function test_endpoint_list_filtrato_per_stato(): void
    {
        Segnalazione::create([
            'foto' => [],
            'tipo' => 'perdita d\'acqua',
            'lat' => 42.4097,
            'lng' => 12.8607,
            'descrizione' => 'Prima segnalazione',
            'status' => 'approved',
            'is_resolved' => false,
        ]);

        Segnalazione::create([
            'foto' => [],
            'tipo' => 'tombino attappato',
            'lat' => 42.4100,
            'lng' => 12.8610,
            'descrizione' => 'Seconda segnalazione',
            'status' => 'approved',
            'is_resolved' => false,
        ]);

        Segnalazione::create([
            'foto' => [],
            'tipo' => 'buca stradale',
            'lat' => 42.4095,
            'lng' => 12.8605,
            'descrizione' => 'Terza segnalazione',
            'status' => 'pending',
            'is_resolved' => false,
        ]);

        $response = $this->getJson('/api/segnalazioni?status=approved');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(2, $data);
    }

    public function test_endpoint_list_filtrato_per_tipo(): void
    {
        Segnalazione::create([
            'foto' => [],
            'tipo' => 'perdita d\'acqua',
            'lat' => 42.4097,
            'lng' => 12.8607,
            'descrizione' => 'Perdita acqua 1',
            'status' => 'pending',
            'is_resolved' => false,
        ]);

        Segnalazione::create([
            'foto' => [],
            'tipo' => 'perdita d\'acqua',
            'lat' => 42.4100,
            'lng' => 12.8610,
            'descrizione' => 'Perdita acqua 2',
            'status' => 'pending',
            'is_resolved' => false,
        ]);

        Segnalazione::create([
            'foto' => [],
            'tipo' => 'tombino attappato',
            'lat' => 42.4095,
            'lng' => 12.8605,
            'descrizione' => 'Tombino',
            'status' => 'pending',
            'is_resolved' => false,
        ]);

        $response = $this->getJson('/api/segnalazioni?tipo=perdita+d%27acqua');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(2, $data);
    }

    public function test_endpoint_list_filtrato_per_is_resolved(): void
    {
        Segnalazione::create([
            'foto' => [],
            'tipo' => 'perdita d\'acqua',
            'lat' => 42.4097,
            'lng' => 12.8607,
            'descrizione' => 'Prima segnalazione',
            'status' => 'pending',
            'is_resolved' => true,
        ]);

        Segnalazione::create([
            'foto' => [],
            'tipo' => 'tombino attappato',
            'lat' => 42.4100,
            'lng' => 12.8610,
            'descrizione' => 'Seconda segnalazione',
            'status' => 'pending',
            'is_resolved' => true,
        ]);

        Segnalazione::create([
            'foto' => [],
            'tipo' => 'buca stradale',
            'lat' => 42.4095,
            'lng' => 12.8605,
            'descrizione' => 'Terza segnalazione',
            'status' => 'pending',
            'is_resolved' => false,
        ]);

        $response = $this->getJson('/api/segnalazioni?is_resolved=true');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(2, $data);
    }

    public function test_endpoint_updateStatus_approva_segnalazione(): void
    {
        $segnalazione = Segnalazione::create([
            'foto' => [],
            'tipo' => 'perdita d\'acqua',
            'lat' => 42.4097,
            'lng' => 12.8607,
            'descrizione' => 'Perdita d\'acqua',
            'status' => 'pending',
            'is_resolved' => false,
        ]);

        $response = $this->putJson("/api/segnalazioni/{$segnalazione->id}/status", [
            'status' => 'approved',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Stato aggiornato con successo',
        ]);
        $this->assertEquals('approved', Segnalazione::find($segnalazione->id)->status);
    }

    public function test_endpoint_updateStatus_rifiuta_segnalazione(): void
    {
        $segnalazione = Segnalazione::create([
            'foto' => [],
            'tipo' => 'perdita d\'acqua',
            'lat' => 42.4097,
            'lng' => 12.8607,
            'descrizione' => 'Perdita d\'acqua',
            'status' => 'pending',
            'is_resolved' => false,
        ]);

        $response = $this->putJson("/api/segnalazioni/{$segnalazione->id}/status", [
            'status' => 'rejected',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('rejected', Segnalazione::find($segnalazione->id)->status);
    }

    public function test_endpoint_updateStatus_invalido(): void
    {
        $segnalazione = Segnalazione::create([
            'foto' => [],
            'tipo' => 'perdita d\'acqua',
            'lat' => 42.4097,
            'lng' => 12.8607,
            'descrizione' => 'Perdita d\'acqua',
            'status' => 'pending',
            'is_resolved' => false,
        ]);

        $response = $this->putJson("/api/segnalazioni/{$segnalazione->id}/status", [
            'status' => 'invalido',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('status');
    }

    public function test_endpoint_updateStatus_non_trovata(): void
    {
        $response = $this->putJson('/api/segnalazioni/99999/status', [
            'status' => 'approved',
        ]);

        $response->assertStatus(404);
        $response->assertJson([
            'error' => 'Segnalazione non trovata',
        ]);
    }

    public function test_endpoint_store_invio_email_approvazione_richiesta(): void
    {
        Mail::fake();

        $this->postJson('/api/segnalazioni', [
            'tipo' => 'perdita d\'acqua',
            'lat' => 42.4097,
            'lng' => 12.8607,
            'descrizione' => 'Test invio email',
            'foto' => [],
        ]);

        Mail::assertSent(\App\Mail\ApprovalRequestMail::class);
    }
}
