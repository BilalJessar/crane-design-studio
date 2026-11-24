<?php

namespace Tests\Unit;

use App\Services\CraneGeometryService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CraneGeometryServiceTest extends TestCase
{
    public static function designs(): array
    {
        return [
            'sample' => [15000, 500, 300, 2500, 6000, 5],
            'minimum' => [3000, 100, 100, 500, 1000, 0.5],
            'maximum' => [30000, 2000, 1500, 6000, 20000, 20],
            'girder wider than carriage' => [3001, 1999, 1499, 501, 1001, 1.25],
            'carriage longer than span' => [3000, 100, 100, 6000, 20000, 20],
        ];
    }

    #[DataProvider('designs')]
    public function test_geometry_matches_inputs_and_fits_uniformly_scaled_views(
        int $span, int $depth, int $width, int $length, int $lift, float $capacity
    ): void {
        $p = ['name' => 'Crane', 'crane_type' => 'single_girder', 'capacity' => $capacity,
            'span' => $span, 'girder_depth' => $depth, 'girder_width' => $width,
            'end_carriage_length' => $length, 'lifting_height' => $lift];
        $service = new CraneGeometryService;
        $drawing = $service->generate($p);
        $this->assertSame($drawing, $service->generate($p));
        $this->assertSame($p, $drawing['parameters']);
        $this->assertJson(json_encode($drawing, JSON_THROW_ON_ERROR));

        foreach (['front' => $depth, 'top' => $width] as $name => $height) {
            $view = $drawing['views'][$name];
            $shapes = array_column($view['components'], 'geometry', 'id');
            $this->assertEquals($span, $shapes['right_rail_centerline']['x1'] - $shapes['left_rail_centerline']['x1']);
            $this->assertEquals($span, $shapes['girder']['width']);
            $this->assertEquals($height, $shapes['girder']['height']);
            $this->assertSame('xMidYMid meet', $view['preserveAspectRatio']);
            if ($name === 'top') {
                foreach (['left' => 0, 'right' => $span] as $side => $railX) {
                    $carriage = $shapes[$side.'_carriage'];
                    $this->assertEquals($railX, $carriage['x'] + $carriage['width'] / 2);
                    $this->assertEquals($length, $carriage['height']);
                }
            } else {
                $this->assertSame("Required lifting height: $lift mm", $shapes['lifting_height']['text']);
                $this->assertArrayHasKey('hoist', $shapes);
                $this->assertArrayHasKey('hook_tip', $shapes);
            }

            $expected = $name === 'top' ? ['span', 'end_carriage_length', 'girder_width'] : ['span', 'girder_depth'];
            $this->assertSame($expected, array_column($view['dimensions'], 'id'));
            $items = $view['components'];
            foreach ($view['dimensions'] as $dimension) {
                $value = $p[$dimension['id']];
                $this->assertSame($value, $dimension['value']);
                $this->assertSame("$value mm", $dimension['label']['geometry']['text']);
                $line = $dimension['line']['geometry'];
                $this->assertEquals($value, hypot($line['x2'] - $line['x1'], $line['y2'] - $line['y1']));
                array_push($items, $dimension['line'], $dimension['label']);
            }

            $box = $view['viewBox'];
            $this->assertGreaterThan(0, $box['width']);
            $this->assertGreaterThan(0, $box['height']);
            foreach ($items as $item) {
                $g = $item['geometry'];
                $points = match ($item['type']) {
                    'line' => [[$g['x1'], $g['y1']], [$g['x2'], $g['y2']]],
                    'rectangle' => [[$g['x'], $g['y']], [$g['x'] + $g['width'], $g['y'] + $g['height']]],
                    'text' => [[$g['x'], $g['y']]],
                };
                foreach ($points as [$x, $y]) {
                    $this->assertGreaterThanOrEqual($box['x'], $x);
                    $this->assertLessThanOrEqual($box['x'] + $box['width'], $x);
                    $this->assertGreaterThanOrEqual($box['y'], $y);
                    $this->assertLessThanOrEqual($box['y'] + $box['height'], $y);
                }
            }
        }
    }
}
