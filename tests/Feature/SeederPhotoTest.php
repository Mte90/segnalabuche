<?php

namespace Tests\Feature;

use App\Models\Segnalazione;
use Database\Seeders\TestSegnalazioneSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SeederPhotoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_seeder_creates_segnalazioni_with_photo_paths_in_storage(): void
    {
        $this->artisan('db:seed', ['--class' => TestSegnalazioneSeeder::class])->assertExitCode(0);

        $this->assertEquals(5, Segnalazione::count());

        $segnalazioniWithPhotos = Segnalazione::where('status', '!=', 'approved')
            ->orWhere('status', 'rejected')
            ->get();

        foreach ($segnalazioniWithPhotos as $segnalazione) {
            $fotoPaths = $segnalazione->foto;

            $this->assertIsArray($fotoPaths);
            $this->assertGreaterThan(0, count($fotoPaths));

            foreach ($fotoPaths as $path) {
                $this->assertStringStartsWith('segnalazioni/', $path);
                $this->assertTrue(Storage::disk('public')->exists($path));
            }
        }
    }

    public function test_seeder_foto_column_contains_valid_local_paths(): void
    {
        // Run the seeder
        $this->artisan('db:seed', ['--class' => TestSegnalazioneSeeder::class])->assertExitCode(0);

        // Get the first segnalazione with photos
        $segnalazione = Segnalazione::whereNotNull('foto')
            ->first();

        $this->assertNotNull($segnalazione);
        $this->assertNotNull($segnalazione->foto);

        $fotoPaths = $segnalazione->foto;

        // Verify foto is an array of paths
        $this->assertIsArray($fotoPaths);
        $this->assertGreaterThan(0, count($fotoPaths));

        // Verify each path is a valid local storage path
        foreach ($fotoPaths as $path) {
            $this->assertIsString($path);
            $this->assertStringStartsWith('segnalazioni/', $path);
            $this->assertTrue(Storage::disk('public')->exists($path));
        }
    }

    public function test_seeder_photo_files_physically_exist_in_storage(): void
    {
        // Run the seeder
        $this->artisan('db:seed', ['--class' => TestSegnalazioneSeeder::class])->assertExitCode(0);

        // Verify photo files exist in storage/app/public/segnalazioni/
        $segnalazioni = Segnalazione::whereNotNull('foto')
            ->get();

        $photoCount = 0;
        foreach ($segnalazioni as $segnalazione) {
            foreach ($segnalazione->foto as $path) {
                $this->assertTrue(Storage::disk('public')->exists($path), "Photo file not found: $path");
                $photoCount++;
            }
        }

        // At least some photos should exist (from the fixture files)
        $this->assertGreaterThan(0, $photoCount);
    }

    public function test_seeder_empty_foto_array_for_approved_segna(): void
    {
        // Run the seeder
        $this->artisan('db:seed', ['--class' => TestSegnalazioneSeeder::class])->assertExitCode(0);

        // Get the segnalazione that should have empty foto array
        $segnalazione = Segnalazione::where('tipo', 'illuminazione pubblica')->first();

        $this->assertNotNull($segnalazione);
        $this->assertEquals([], $segnalazione->foto);
    }
}
