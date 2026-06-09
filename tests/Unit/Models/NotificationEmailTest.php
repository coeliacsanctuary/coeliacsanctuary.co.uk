<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use PHPUnit\Framework\Attributes\Test;
use App\Models\NotificationEmail;
use App\Models\Shop\ShopCustomer;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationEmailTest extends TestCase
{
    #[Test]
    public function itCreatesAUuidForTheKeyIfNotSpecified(): void
    {
        $model = $this->create(NotificationEmail::class);

        $this->assertNotNull($model->key);
        $this->assertTrue(Str::isUuid($model->key));
    }

    #[Test]
    public function itCastsTheEmailData(): void
    {
        /** @var NotificationEmail $model */
        $model = $this->create(NotificationEmail::class);

        $this->assertIsArray($model->data);
        $this->assertArrayHasKey('date', $model->data);
        $this->assertInstanceOf(Carbon::class, $model->data['date']);
    }

    #[Test]
    public function itPassesThroughScalarValuesInEmailData(): void
    {
        /** @var NotificationEmail $model */
        $model = $this->build(NotificationEmail::class)
            ->create([
                'data' => [
                    'date' => now()->toString(),
                    'reviewLink' => 'https://www.coeliacsanctuary.co.uk/review/abc123',
                    'delayText' => '5 days',
                    'reason' => 'to invite you to leave a review',
                ],
            ]);

        $this->assertArrayHasKey('reviewLink', $model->data);
        $this->assertSame('https://www.coeliacsanctuary.co.uk/review/abc123', $model->data['reviewLink']);
        $this->assertArrayHasKey('delayText', $model->data);
        $this->assertSame('5 days', $model->data['delayText']);
        $this->assertArrayHasKey('reason', $model->data);
    }

    #[Test]
    public function itCanBeAssociatedToAShopCustomer(): void
    {
        /** @var NotificationEmail $model */
        $model = $this->create(NotificationEmail::class);

        $this->assertInstanceOf(ShopCustomer::class, $model->customer);
    }
}
