<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class AdminValidationTest extends TestCase
{
    public function latProvider(): array
    {
        return [
            [0.0],
            [90.0],
            [-90.0],
            [42.4097],
            [-42.4097],
            [0.001],
            [-0.001],
        ];
    }

    public function lngProvider(): array
    {
        return [
            [0.0],
            [180.0],
            [-180.0],
            [12.8607],
            [-12.8607],
            [0.001],
            [-0.001],
        ];
    }

    public function invalidLatProvider(): array
    {
        return [
            [90.1],
            [-90.1],
            [100.0],
            [-100.0],
        ];
    }

    public function invalidLngProvider(): array
    {
        return [
            [180.1],
            [-180.1],
            [200.0],
            [-200.0],
        ];
    }

    public function nonNumericProvider(): array
    {
        return [
            ['42'],
            [''],
            [null],
            [[]],
            [new \StdClass],
            [INF],
            [NAN],
        ];
    }

    public function test_valid_latitude_returns_true(): void
    {
        $latitudes = [0.0, 90.0, -90.0, 42.4097, -42.4097, 0.001, -0.001];
        foreach ($latitudes as $lat) {
            $result = $this->validateLatitude($lat);
            $this->assertTrue($result, "Latitude $lat should be valid");
        }
    }

    public function test_valid_longitude_returns_true(): void
    {
        $longitudes = [0.0, 180.0, -180.0, 12.8607, -12.8607, 0.001, -0.001];
        foreach ($longitudes as $lng) {
            $result = $this->validateLongitude($lng);
            $this->assertTrue($result, "Longitude $lng should be valid");
        }
    }

    public function test_invalid_latitude_returns_false(): void
    {
        $latitudes = [90.1, -90.1, 100.0, -100.0];
        foreach ($latitudes as $lat) {
            $result = $this->validateLatitude($lat);
            $this->assertFalse($result, "Latitude $lat should be invalid (out of range)");
        }
    }

    public function test_invalid_longitude_returns_false(): void
    {
        $longitudes = [180.1, -180.1, 200.0, -200.0];
        foreach ($longitudes as $lng) {
            $result = $this->validateLongitude($lng);
            $this->assertFalse($result, "Longitude $lng should be invalid (out of range)");
        }
    }

    public function test_non_numeric_latitude_returns_false(): void
    {
        $values = ['', null, [], new \StdClass];
        foreach ($values as $lat) {
            $result = $this->validateLatitude($lat);
            $this->assertFalse($result, 'Non-numeric latitude should be invalid');
        }
    }

    public function test_non_numeric_longitude_returns_false(): void
    {
        $values = ['', null, [], new \StdClass];
        foreach ($values as $lng) {
            $result = $this->validateLongitude($lng);
            $this->assertFalse($result, 'Non-numeric longitude should be invalid');
        }
    }

    public function test_complete_validation_with_valid_coordinates(): void
    {
        $data = [
            'tipo' => 'perdita d\'acqua',
            'status' => 'pending',
            'lat' => 42.4097,
            'lng' => 12.8607,
        ];

        $result = $this->validateCoordinates($data);
        $this->assertTrue($result, 'Valid complete coordinates should pass validation');
    }

    public function test_complete_validation_with_missing_fields(): void
    {
        $data = [
            'lat' => 42.4097,
            'lng' => 12.8607,
        ];

        $result = $this->validateCoordinates($data);
        $this->assertFalse($result, 'Missing required fields should fail validation');
    }

    public function test_complete_validation_with_empty_coords(): void
    {
        $data = [
            'tipo' => 'perdita d\'acqua',
            'status' => 'pending',
            'lat' => '',
            'lng' => '',
        ];

        $result = $this->validateCoordinates($data);
        $this->assertFalse($result, 'Empty coordinates should fail validation');
    }

    public function test_coordinate_boundaries(): void
    {
        $corners = [
            ['lat' => 90.0, 'lng' => 180.0],
            ['lat' => 90.0, 'lng' => -180.0],
            ['lat' => -90.0, 'lng' => 180.0],
            ['lat' => -90.0, 'lng' => -180.0],
        ];

        foreach ($corners as $corner) {
            $data = [
                'tipo' => 'test',
                'status' => 'pending',
                'lat' => $corner['lat'],
                'lng' => $corner['lng'],
            ];

            $result = $this->validateCoordinates($data);
            $this->assertTrue($result, 'Boundary coordinates lat='.$corner['lat'].', lng='.$corner['lng'].' should be valid');
        }
    }

    private function validateLatitude($lat): bool
    {
        if (! is_numeric($lat)) {
            return false;
        }

        $lat = (float) $lat;

        if ($lat < -90 || $lat > 90) {
            return false;
        }

        return true;
    }

    private function validateLongitude($lng): bool
    {
        if (! is_numeric($lng)) {
            return false;
        }

        $lng = (float) $lng;

        if ($lng < -180 || $lng > 180) {
            return false;
        }

        return true;
    }

    private function validateCoordinates(array $data): bool
    {
        if (empty($data['tipo']) || empty($data['status'])) {
            return false;
        }

        if (empty($data['lat']) || empty($data['lng'])) {
            return false;
        }

        if (! is_numeric($data['lat']) || ! is_numeric($data['lng'])) {
            return false;
        }

        $lat = (float) $data['lat'];
        $lng = (float) $data['lng'];

        if ($lat < -90 || $lat > 90) {
            return false;
        }

        if ($lng < -180 || $lng > 180) {
            return false;
        }

        return true;
    }
}
