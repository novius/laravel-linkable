<?php

namespace Novius\LaravelLinkable\Tests;

use Novius\LaravelLinkable\Tests\Models\LinkableModel;

class LinkableTest extends TestCase
{
    /* --- LinkableTest Tests --- */

    /** @test */
    public function model_resolve_binding_ok(): void
    {
        $linkable = LinkableModel::factory()->contextDefault()->published()->create();
        $response = $this->get('/model/'.$linkable->id);

        $response->assertStatus(200);
    }

    /** @test */
    public function model_resolve_binding_ko_published(): void
    {
        $linkable = LinkableModel::factory()->contextDefault()->create();
        $response = $this->get('/model/'.$linkable->id);

        $response->assertStatus(404);
    }

    /** @test */
    public function model_resolve_binding_ko_context(): void
    {
        $linkable = LinkableModel::factory()->published()->create();
        $response = $this->get('/model/'.$linkable->id);

        $response->assertStatus(404);
    }

    /** @test */
    public function model_resolve_binding_preview_token(): void
    {
        $linkable = LinkableModel::factory()->contextDefault()->create();
        $response = $this->get('/model/'.$linkable->id.'?previewToken='.$linkable->preview_token);

        $response->assertStatus(200);
    }
}
