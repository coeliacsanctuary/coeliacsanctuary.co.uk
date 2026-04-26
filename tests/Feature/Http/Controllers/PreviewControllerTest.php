<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\NovaPreview;
use App\Models\User;
use App\Support\NovaPreview\NovaPreviewResolver;
use App\Support\NovaPreview\Renderer;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PreviewControllerTest extends TestCase
{
    protected function actingAsUser(): static
    {
        return $this->actingAs($this->create(User::class));
    }

    protected function createBlogPreview(array $payloadOverrides = []): NovaPreview
    {
        return $this->create(NovaPreview::class, [
            'model' => 'blog',
            'payload' => array_merge([
                'title' => 'Preview Blog Title',
                'description' => 'Preview description.',
                'body' => '<p>Preview body.</p>',
                'meta_tags' => 'gluten-free',
                'meta_description' => 'Preview meta description.',
                'primary_image_url' => 'https://example.com/image.jpg',
                'social_image_url' => 'https://example.com/social.jpg',
                'show_author' => true,
            ], $payloadOverrides),
        ]);
    }

    protected function mockResolverReturning(string $component, array $payload): void
    {
        $mockRenderer = \Mockery::mock(Renderer::class);
        $mockRenderer->shouldReceive('component')->andReturn($component);
        $mockRenderer->shouldReceive('payload')->andReturn($payload);

        $this->mock(NovaPreviewResolver::class)
            ->shouldReceive('handle')
            ->andReturn($mockRenderer);
    }

    #[Test]
    public function itRequiresAuthentication(): void
    {
        $preview = $this->createBlogPreview();

        $this->get(route('nova-preview.show', $preview->token))->assertRedirect();
    }

    #[Test]
    public function itReturnsNotFoundForAnUnknownToken(): void
    {
        $this->actingAsUser()
            ->get(route('nova-preview.show', 'unknown-token'))
            ->assertNotFound();
    }

    #[Test]
    public function itCallsTheNovaPreviewResolverWithTheCorrectModel(): void
    {
        $preview = $this->createBlogPreview();

        $mockRenderer = \Mockery::mock(Renderer::class);
        $mockRenderer->shouldReceive('component')->andReturn('Blog/Preview');
        $mockRenderer->shouldReceive('payload')->andReturn(['blog' => []]);

        $this->mock(NovaPreviewResolver::class)
            ->shouldReceive('handle')
            ->with('blog')
            ->once()
            ->andReturn($mockRenderer);

        $this->actingAsUser()
            ->get(route('nova-preview.show', $preview->token));
    }

    #[Test]
    public function itRendersTheComponentReturnedByTheResolver(): void
    {
        $preview = $this->createBlogPreview();

        $this->mockResolverReturning('Blog/Preview', ['blog' => []]);

        $this->actingAsUser()
            ->get(route('nova-preview.show', $preview->token))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Blog/Preview'));
    }

    #[Test]
    public function itPassesThePayloadReturnedByTheResolver(): void
    {
        $preview = $this->createBlogPreview();

        $this->mockResolverReturning('Blog/Preview', [
            'blog' => ['title' => 'Mocked Title', 'tags' => []],
        ]);

        $this->actingAsUser()
            ->get(route('nova-preview.show', $preview->token))
            ->assertInertia(fn (Assert $page) => $page
                ->has('blog')
                ->where('blog.title', 'Mocked Title')
            );
    }
}
