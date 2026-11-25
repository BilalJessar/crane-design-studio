<?php

namespace Tests\Feature;

use App\Models\CraneDesign;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class CraneDesignApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_collection(): void
    {
        $this->get('/api/designs')->assertOk()->assertExactJson(['data' => []]);
    }

    public function test_list_orders_by_update_time_then_id_descending(): void
    {
        $latest = CraneDesign::factory()->create(['updated_at' => now()->addDay()]);
        [$older, $tie] = CraneDesign::factory()->count(2)->create(['updated_at' => now()]);

        $this->get('/api/designs')->assertOk()->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.id', $latest->id)
            ->assertJsonPath('data.1.id', $tie->id)
            ->assertJsonPath('data.2.id', $older->id);
    }

    public function test_create_retrieve_replace_and_delete(): void
    {
        $this->freezeTime();
        $input = CraneDesign::factory()->raw(['capacity' => 5.25]);
        $created = $this->postJson('/api/designs', $input + ['id' => 999])->assertCreated();
        $id = $created->json('data.id');
        $this->assertIsInt($id);
        $this->assertNotSame(999, $id);
        $url = "/api/designs/$id";
        $metadata = ['id' => $id, 'updated_at' => now()->toIso8601String()];
        $expected = ['data' => $metadata + $input];
        $created->assertExactJson($expected);
        $this->get($url)->assertOk()->assertExactJson($expected);
        $this->assertDatabaseHas('crane_designs', ['id' => $id] + $input);

        $updated = [
            'name' => 'Revised drawing', 'crane_type' => 'single_girder', 'capacity' => 6,
            'span' => 12000, 'lifting_height' => 4500, 'girder_depth' => 600,
            'girder_width' => 350, 'end_carriage_length' => 2200,
        ];
        $this->putJson($url, $updated)->assertOk()->assertExactJson(['data' => $metadata + $updated]);
        $this->assertDatabaseCount('crane_designs', 1);
        $this->assertDatabaseHas('crane_designs', ['id' => $id] + $updated);
        $other = CraneDesign::factory()->create();
        $this->delete($url)->assertNoContent();
        $this->assertDatabaseMissing('crane_designs', ['id' => $id]);
        $this->assertModelExists($other);
        $this->assertDatabaseCount('crane_designs', 1);
    }

    public function test_reopened_parameters_regenerate_both_views(): void
    {
        $design = CraneDesign::factory()->create();
        $saved = $this->getJson("/api/designs/$design->id")->assertOk()->json('data');
        $this->postJson('/api/designs/generate', $saved)->assertOk()
            ->assertJsonPath('data.parameters.span', $design->span)
            ->assertJsonMissingPath('data.parameters.updated_at')
            ->assertJsonStructure(['data' => ['views' => ['front', 'top']]]);
        $this->assertDatabaseCount('crane_designs', 1);
    }

    public function test_all_fields_are_required_on_create_and_update_without_accept_header(): void
    {
        $design = CraneDesign::factory()->create();
        $original = $design->fresh()->getAttributes();
        $fields = array_keys(CraneDesign::factory()->raw());
        $this->post('/api/designs', [])->assertUnprocessable()->assertJsonValidationErrors($fields);
        $this->put("/api/designs/$design->id", [])->assertUnprocessable()->assertJsonValidationErrors($fields);
        $this->assertDatabaseCount('crane_designs', 1);
        $this->assertSame($original, $design->fresh()->getAttributes());
    }

    public static function invalidInputs(): iterable
    {
        foreach ([
            'name' => ['', str_repeat('x', 151), ['not a string']],
            'crane_type' => ['double_girder', true],
            'capacity' => [-1, 0, 0.49, 20.01, 5.125, 'five', true],
            'span' => [-1, 2999, 30001, 'wide', 4000.5],
            'lifting_height' => [999, 20001],
            'girder_depth' => [99, 2001],
            'girder_width' => [99, 1501],
            'end_carriage_length' => [499, 6001],
        ] as $field => $values) {
            foreach ($values as $value) {
                yield $field.' '.json_encode($value) => [$field, $value];
            }
        }
    }

    #[DataProvider('invalidInputs')]
    public function test_invalid_writes_preserve_database(string $field, mixed $value): void
    {
        $design = CraneDesign::factory()->create();
        $original = $design->fresh()->getAttributes();
        $invalid = CraneDesign::factory()->raw([$field => $value]);
        $this->postJson('/api/designs', $invalid)->assertUnprocessable()->assertJsonValidationErrors($field);
        $this->putJson("/api/designs/$design->id", $invalid)->assertUnprocessable()->assertJsonValidationErrors($field);
        $this->assertDatabaseCount('crane_designs', 1);
        $this->assertSame($original, $design->fresh()->getAttributes());
    }

    public function test_inclusive_boundaries(): void
    {
        $this->postJson('/api/designs', [
            'name' => 'A', 'crane_type' => 'single_girder', 'capacity' => 0.5,
            'span' => 3000, 'lifting_height' => 1000, 'girder_depth' => 100,
            'girder_width' => 100, 'end_carriage_length' => 500,
        ])->assertCreated();

        $this->postJson('/api/designs', [
            'name' => str_repeat('x', 150), 'crane_type' => 'single_girder', 'capacity' => 20,
            'span' => 30000, 'lifting_height' => 20000, 'girder_depth' => 2000,
            'girder_width' => 1500, 'end_carriage_length' => 6000,
        ])->assertCreated();
    }

    public function test_missing_design_returns_json_on_all_member_routes(): void
    {
        $url = '/api/designs/999';
        foreach ([$this->get($url), $this->put($url, []), $this->delete($url)] as $response) {
            $response->assertNotFound()->assertExactJson(['message' => 'Design not found.']);
        }
    }

    public function test_unexpected_failure_returns_generic_json_with_debug_disabled(): void
    {
        config(['app.debug' => false]);
        CraneDesign::creating(function () {
            throw new RuntimeException('Internal diagnostic information');
        });

        $this->post('/api/designs', CraneDesign::factory()->raw())
            ->assertInternalServerError()->assertExactJson(['message' => 'Server Error']);
    }
}
