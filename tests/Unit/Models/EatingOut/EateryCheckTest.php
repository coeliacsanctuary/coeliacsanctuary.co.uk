<?php

declare(strict_types=1);

namespace Tests\Unit\Models\EatingOut;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCheck;
use Carbon\Carbon;
use Database\Seeders\EateryScaffoldingSeeder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateryCheckTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);
    }

    #[Test]
    public function itHasAnEateryRelationship(): void
    {
        $eatery = $this->create(Eatery::class);

        $check = $this->build(EateryCheck::class)->create([
            'wheretoeat_id' => $eatery->id,
        ]);

        $this->assertInstanceOf(Eatery::class, $check->eatery);
        $this->assertTrue($eatery->is($check->eatery));
    }

    #[Test]
    public function itCastsWebsiteCheckedAtToDatetime(): void
    {
        $eatery = $this->create(Eatery::class);

        $check = $this->build(EateryCheck::class)->websiteChecked()->create([
            'wheretoeat_id' => $eatery->id,
        ]);

        $this->assertInstanceOf(Carbon::class, $check->website_checked_at);
    }

    #[Test]
    public function itCastsGoogleCheckedAtToDatetime(): void
    {
        $eatery = $this->create(Eatery::class);

        $check = $this->build(EateryCheck::class)->googleChecked()->create([
            'wheretoeat_id' => $eatery->id,
        ]);

        $this->assertInstanceOf(Carbon::class, $check->google_checked_at);
    }
}
