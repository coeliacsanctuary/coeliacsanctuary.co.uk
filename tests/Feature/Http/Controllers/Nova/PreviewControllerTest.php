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
    public function itRequiresAPrimaryImageUrl(): void
    {
        $this->actingAsNovaUser()
            ->postJson($this->endpoint, $this->validPayload(['primary_image_url' => null]))
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('primary_image_url');
    }

    #[Test]
    public function itAcceptsADataUriForPrimaryImageUrl(): void
    {
        $this->actingAsNovaUser()
            ->postJson($this->endpoint, $this->validPayload(['primary_image_url' => 'data:image/jpeg;base64,/9j/4AAQ']))
            ->assertOk();
    }

    #[Test]
    public function itAcceptsNullSocialImageUrl(): void
    {
        $this->actingAsNovaUser()
            ->postJson($this->endpoint, $this->validPayload(['social_image_url' => null]))
            ->assertOk();
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
    public function itAcceptsAnArrayOfBodyImages(): void
    {
        $this->actingAsNovaUser()
            ->postJson($this->endpoint, $this->validPayload([
                'body_images' => [
                    ['file_name' => 'a.jpg', 'url' => 'https://example.com/a.jpg'],
                    ['file_name' => 'b.jpg', 'url' => 'https://example.com/b.jpg'],
                ],
            ]))
            ->assertOk();
    }

    #[Test]
    public function itAcceptsAnEmptyBodyImagesArray(): void
    {
        $this->actingAsNovaUser()
            ->postJson($this->endpoint, $this->validPayload(['body_images' => []]))
            ->assertOk();
    }

    #[Test]
    public function itRejectsBodyImagesWithoutAFileName(): void
    {
        $this->actingAsNovaUser()
            ->postJson($this->endpoint, $this->validPayload([
                'body_images' => [['url' => 'https://example.com/a.jpg']],
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('body_images.0.file_name');
    }

    #[Test]
    public function itStoresBodyImagesInThePayload(): void
    {
        $this->actingAsNovaUser()
            ->postJson($this->endpoint, $this->validPayload([
                'body_images' => [['file_name' => 'a.jpg', 'url' => 'https://example.com/a.jpg']],
            ]));

        $preview = NovaPreview::query()->first();

        $this->assertEquals(
            [['file_name' => 'a.jpg', 'url' => 'https://example.com/a.jpg']],
            $preview->payload['body_images']
        );
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
