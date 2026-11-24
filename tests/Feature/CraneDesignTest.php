<?php

namespace Tests\Feature;

use App\Models\CraneDesign;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CraneDesignTest extends TestCase
{
    use RefreshDatabase;

    public function test_migration_creates_the_design_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('crane_designs', [
            'id', 'name', 'crane_type', 'capacity', 'span', 'lifting_height',
            'girder_depth', 'girder_width', 'end_carriage_length', 'created_at', 'updated_at',
        ]));
    }

    public function test_design_can_be_created_and_retrieved_with_the_default_type(): void
    {
        $attributes = CraneDesign::factory()->raw(['name' => 'Saved design']);
        unset($attributes['crane_type']);

        $design = CraneDesign::create($attributes);
        $saved = CraneDesign::findOrFail($design->id);

        $this->assertSame('Saved design', $saved->name);
        $this->assertSame('single_girder', $saved->crane_type);
        $this->assertNotNull($saved->created_at);
        $this->assertNotNull($saved->updated_at);
    }

    public function test_database_rejects_unsupported_crane_types(): void
    {
        $this->expectException(QueryException::class);

        CraneDesign::factory()->create(['crane_type' => 'double_girder']);
    }

    public function test_factory_design_can_be_updated_and_deleted(): void
    {
        $design = CraneDesign::factory()->create();
        $design->update(['name' => 'Revised design', 'span' => 12500]);

        $this->assertDatabaseHas('crane_designs', [
            'id' => $design->id, 'name' => 'Revised design', 'span' => 12500,
        ]);

        $design->delete();
        $this->assertModelMissing($design);
    }

    public function test_measurements_are_cast_to_the_expected_types(): void
    {
        $design = CraneDesign::factory()->make([
            'capacity' => '5.5',
            'span' => '15000',
            'lifting_height' => '6000',
            'girder_depth' => '500',
            'girder_width' => '300',
            'end_carriage_length' => '2500',
        ]);

        $this->assertSame('5.50', $design->capacity);
        $this->assertSame(15000, $design->span);
        $this->assertSame(6000, $design->lifting_height);
        $this->assertSame(500, $design->girder_depth);
        $this->assertSame(300, $design->girder_width);
        $this->assertSame(2500, $design->end_carriage_length);
    }

    public function test_registered_seeder_is_repeatable_and_preserves_existing_values(): void
    {
        $this->seed();
        $this->assertDatabaseHas('crane_designs', [
            'name' => 'Crane 001', 'crane_type' => 'single_girder', 'capacity' => 5,
            'span' => 15000, 'lifting_height' => 6000, 'girder_depth' => 500,
            'girder_width' => 300, 'end_carriage_length' => 2500,
        ]);
        CraneDesign::where('name', 'Crane 001')->update(['span' => 14500]);

        $this->seed();
        $this->assertSame(['Crane 001', 'Crane 002', 'Crane 003'],
            CraneDesign::orderBy('name')->pluck('name')->all());
        $this->assertDatabaseHas('crane_designs', ['name' => 'Crane 001', 'span' => 14500]);
        $this->assertDatabaseCount('users', 1);
    }
}
