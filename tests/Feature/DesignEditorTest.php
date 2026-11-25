<?php

namespace Tests\Feature;

use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DesignEditorTest extends TestCase
{
    public function test_editor_receives_shared_design_limits_and_preview_endpoint(): void
    {
        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Designs/Editor')
            ->where('designId', null)
            ->where('limits', fn ($limits) => $limits->all() === config('crane_design'))
            ->where('previewUrl', '/api/designs/generate'));
    }

    public function test_saved_designs_page_loads(): void
    {
        $this->get('/designs')->assertOk()->assertInertia(fn (Assert $page) => $page->component('Designs/Index'));
    }

    public function test_edit_url_supplies_the_id_to_the_shared_editor(): void
    {
        $this->get('/designs/42/edit')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Designs/Editor')->where('designId', '42'));
    }
}
