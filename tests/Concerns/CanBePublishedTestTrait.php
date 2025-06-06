<?php

declare(strict_types=1);

namespace Tests\Concerns;

use PHPUnit\Framework\Attributes\Test;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Tests\TestCase;

/** @mixin TestCase */
trait CanBePublishedTestTrait
{
    /** @var callable(array): Model */
    protected $factoryClosure;

    /**
     * @param  callable(array $parameters): Model  $factory
     * @param  string  $column
     */
    protected function setUpCanBePublishedModelTest(callable $factory): void
    {
        $this->factoryClosure = $factory;
    }

    private function factory($params = []): Model
    {
        return call_user_func($this->factoryClosure, $params);
    }

    #[Test]
    public function itHasAPublishAtColumn(): void
    {
        $this->assertNotNull($this->factory()->publish_at);
    }

    #[Test]
    public function itCastsThePublishAtColumnToCarbon(): void
    {
        $this->assertInstanceOf(Carbon::class, $this->factory()->publish_at);
    }

    #[Test]
    public function itHasADraftColumn(): void
    {
        $this->assertNotNull($this->factory()->draft);
    }

    #[Test]
    public function itCastsTheDraftColumnAsABool(): void
    {
        $this->assertIsBool($this->factory()->draft);
    }
}
