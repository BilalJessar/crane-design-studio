<?php

namespace Tests\Feature;

use App\Models\CraneDesign;
use App\Services\CraneGeometryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class CraneGeometryPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_preview_returns_both_views_without_writing_designs(): void
    {
        $design = CraneDesign::factory()->create(['name' => 'Crane 001']);
        $before = $design->fresh()->getAttributes();
        $input = ['name' => 'Crane 001', 'crane_type' => 'single_girder', 'capacity' => 5,
            'span' => '15000', 'lifting_height' => 6000, 'girder_depth' => 500,
            'girder_width' => 300, 'end_carriage_length' => 2500];
        $view = ['viewBox' => ['x', 'y', 'width', 'height'], 'preserveAspectRatio',
            'components' => ['*' => ['id', 'type', 'geometry']],
            'dimensions' => ['*' => ['id', 'value', 'line', 'label']]];
        $this->postJson('/api/designs/generate', $input + ['id' => $design->id])
            ->assertOk()->assertJsonStructure(['data' => ['parameters', 'units', 'drawing_type',
                'views' => ['front' => $view, 'top' => $view], 'presentation_assumptions', 'engineering_limitations']])
            ->assertJsonPath('data.parameters.span', 15000)->assertJsonMissingPath('data.parameters.id')
            ->assertJsonPath('data.units.length', 'mm')->assertJsonPath('data.units.capacity', 't')
            ->assertJsonPath('data.drawing_type', 'single_girder_eot');
        $this->assertDatabaseCount('crane_designs', 1);
        $this->assertSame($before, $design->fresh()->getAttributes());
    }

    public function test_preview_reuses_design_validation(): void
    {
        $invalid = CraneDesign::factory()->raw(['span' => 0, 'capacity' => 5.123, 'crane_type' => 'double']);
        $this->post('/api/designs/generate', $invalid)->assertUnprocessable()
            ->assertJsonValidationErrors(['span', 'capacity', 'crane_type']);
        $this->post('/api/designs/generate')->assertUnprocessable()->assertJsonValidationErrors(array_keys($invalid));
        $this->assertDatabaseCount('crane_designs', 0);
    }

    public function test_preview_uses_standard_json_server_error(): void
    {
        config(['app.debug' => false]);
        $this->mock(CraneGeometryService::class)->shouldReceive('generate')->once()
            ->andThrow(new RuntimeException('Internal diagnostic'));
        $this->post('/api/designs/generate', CraneDesign::factory()->raw())
            ->assertInternalServerError()->assertExactJson(['message' => 'Server Error']);
    }
}
