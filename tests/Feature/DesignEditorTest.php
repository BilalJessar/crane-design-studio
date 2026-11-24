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
            ->where('limits', fn ($limits) => $limits->all() === config('crane_design'))
            ->where('previewUrl', '/api/designs/generate'));
    }
}
