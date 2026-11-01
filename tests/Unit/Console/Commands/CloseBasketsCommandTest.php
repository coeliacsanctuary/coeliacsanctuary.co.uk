<?php

declare(strict_types=1);

namespace Tests\Unit\Console\Commands;

use PHPUnit\Framework\Attributes\Test;
use App\Actions\Shop\CloseBasketAction;
use App\Console\Commands\CloseBasketsCommand;
use App\Models\Shop\ShopOrder;
use Carbon\Carbon;
use Tests\TestCase;

class CloseBasketsCommandTest extends TestCase
{
    #[Test]
    public function itDispatchesACloseBasketEventForBasketsThatHaventBeenUpdatedForAnHour(): void
    {
        $this->travelTo('2024-04-01 12:00:00');

        $this->build(ShopOrder::class)->asBasket()->create([
            'updated_at' => '2024-04-01 10:00:00',
        ]);

        $this->expectAction(CloseBasketAction::class);

        $this->artisan(CloseBasketsCommand::class);
    }

    #[Test]
    public function itDoesntDispatchTheEventForBasketsThatHaveRecentlyBeenUpdated(): void
    {
        $this->travelTo('2024-04-01 12:00:00');

        $this->build(ShopOrder::class)->asBasket()->create([
            'created_at' => '2024-04-01 10:30:00',
            'updated_at' => '2024-04-01 11:30:00',
        ]);

        $this->mock(CloseBasketAction::class)->shouldNotReceive('handle');

        $this->artisan(CloseBasketsCommand::class);
    }

    #[Test]
    public function itDoesntDispatchTheEventForEntitiesTharArentBasketState(): void
    {
        $this->travelTo('2024-04-01 12:00:00');

        $this->build(ShopOrder::class)->asPaid()->create([
            'created_at' => '2024-04-01 10:30:00',
            'updated_at' => '2024-04-01 11:30:00',
        ]);

        $this->mock(CloseBasketAction::class)->shouldNotReceive('handle');

        $this->artisan(CloseBasketsCommand::class);
    }
}
