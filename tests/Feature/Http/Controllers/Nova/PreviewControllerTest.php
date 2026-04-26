<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Nova;

use App\Models\NovaPreview;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PreviewControllerTest extends TestCase
{
    protected string $endpoint = '/nova-vendor/preview-button/store';

    protected function validPayload(array $overrides = []): array
    {
        return array_merge([
            'model' => 'blog',
            'title' => 'My Blog Title',
            'description' => 'A blog description.',
            'body' => '<p>Blog body content.</p>',
            'meta_tags' => 'gluten-free',
            'meta_description' => 'A meta description.',
            'primary_image_url' => 'https://example.com/image.jpg',
            'social_image_url' => 'https://example.com/social.jpg',
            'show_author' => true,
        ], $overrides);
    }

    protected function actingAsNovaUser(): static
    {
        return $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itRequiresAValidModel(): void
    {
        $this->actingAsNovaUser()
            ->postJson($this->endpoint, $this->validPayload(['model' => 'invalid']))
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('model');
    }

    #[Test]
    public function itRequiresATitle(): void
    {
        $this->actingAsNovaUser()
            ->postJson($this->endpoint, $this->validPayload(['title' => '']))
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('title');
    }

    #[Test]
    public function itRequiresADescription(): void
    {
        $this->actingAsNovaUser()
            ->postJson($this->endpoint, $this->validPayload(['description' => '']))
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('description');
    }

    #[Test]
    public function itRequiresABody(): void
    {
        $this->actingAsNovaUser()
            ->postJson($this->endpoint, $this->validPayload(['body' => '']))
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('body');
    }

    #[Test]
    public function itRequiresMetaTags(): void
    {
        $this->actingAsNovaUser()
            ->postJson($this->endpoint, $this->validPayload(['meta_tags' => '']))
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('meta_tags');
    }

    #[Test]
    public function itRequiresAMetaDescription(): void
    {
        $this->actingAsNovaUser()
            ->postJson($this->endpoint, $this->validPayload(['meta_description' => '']))
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('meta_description');
    }

    #[Test]
    public function itRequiresAPrimaryImageUrl(): void
    {
        $this->actingAsNovaUser()
            ->postJson($this->endpoint, $this->validPayload(['primary_image_url' => '']))
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('primary_image_url');
    }

    #[Test]
    public function itRequiresAValidUrlForPrimaryImage(): void
    {
        $this->actingAsNovaUser()
            ->postJson($this->endpoint, $this->validPayload(['primary_image_url' => 'not-a-url']))
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('primary_image_url');
    }

    #[Test]
    public function itRequiresASocialImageUrl(): void
    {
        $this->actingAsNovaUser()
            ->postJson($this->endpoint, $this->validPayload(['social_image_url' => '']))
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('social_image_url');
    }

    #[Test]
    public function itRequiresAValidUrlForSocialImage(): void
    {
        $this->actingAsNovaUser()
            ->postJson($this->endpoint, $this->validPayload(['social_image_url' => 'not-a-url']))
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('social_image_url');
    }

    #[Test]
    public function itCreatesANovaPreviewRecordOnSuccess(): void
    {
        $this->assertDatabaseEmpty(NovaPreview::class);

        $this->actingAsNovaUser()
            ->postJson($this->endpoint, $this->validPayload());

        $this->assertDatabaseCount(NovaPreview::class, 1);
    }

    #[Test]
    public function itStoresTheCorrectPayload(): void
    {
        $this->actingAsNovaUser()
            ->postJson($this->endpoint, $this->validPayload());

        $preview = NovaPreview::query()->first();

        $this->assertEquals('blog', $preview->model);
        $this->assertEquals('My Blog Title', $preview->payload['title']);
        $this->assertEquals('A blog description.', $preview->payload['description']);
    }

    #[Test]
    public function itReturnsTheTokenAndPreviewUrl(): void
    {
        $response = $this->actingAsNovaUser()
            ->postJson($this->endpoint, $this->validPayload())
            ->assertOk();

        $preview = NovaPreview::query()->first();

        $response->assertJsonFragment([
            'token' => $preview->token,
            'url' => route('nova-preview.show', $preview->token),
        ]);
    }
}
