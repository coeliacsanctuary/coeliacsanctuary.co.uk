<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Shop\ReviewInvitations;

use App\DataObjects\Shop\ReviewInvitationRule;
use App\Models\Shop\ShopOrder;
use App\Support\Shop\ReviewInvitations\SendingRules;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SendingRulesTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2024-04-15');
    }

    #[Test]
    public function itReturnsACollectionOfReviewInvitationRules(): void
    {
        $rules = SendingRules::get();

        $this->assertCount(4, $rules);

        $rules->each(fn ($rule) => $this->assertInstanceOf(ReviewInvitationRule::class, $rule));
    }

    #[Test]
    public function itLabelsEachRuleWithItsNotificationDelay(): void
    {
        $this->assertSame(
            ['5 days', '10 days', '2 weeks', '3 weeks'],
            SendingRules::get()->pluck('notificationDelayText')->all(),
        );
    }

    #[Test]
    public function itSetsTheMinimumDateForEachRuleRelativeToNow(): void
    {
        $minimumDates = SendingRules::get()
            ->pluck('minimumDate')
            ->map(fn (CarbonInterface $date) => $date->toDateTimeString())
            ->all();

        $this->assertSame([
            '2024-04-10 00:00:00',
            '2024-04-05 00:00:00',
            '2024-04-01 00:00:00',
            '2024-03-25 00:00:00',
        ], $minimumDates);
    }

    #[Test]
    public function theMinimumDatesAreImmutable(): void
    {
        $rule = $this->rule('10 days');

        $rule->minimumDate->addDay();

        $this->assertSame('2024-04-05 00:00:00', $rule->minimumDate->toDateTimeString());
    }

    #[Test]
    public function theDigitalRuleFiltersOnDigitalOnlyOrders(): void
    {
        $this->assertStringContainsString('`is_digital_only` = 1', $this->sqlFor('5 days'));
    }

    #[Test]
    public function theTenDayRuleFiltersOnTheUkPostageArea(): void
    {
        $sql = $this->sqlFor('10 days');

        $this->assertStringContainsString('exists (select * from `shop_postage_countries` where `shop_orders`.`postage_country_id` = `shop_postage_countries`.`id`', $sql);
        $this->assertStringContainsString('and `postage_area_id` = 1)', $sql);
    }

    #[Test]
    public function theTwoWeekRuleFiltersOnTheEuropeanPostageArea(): void
    {
        $sql = $this->sqlFor('2 weeks');

        $this->assertStringContainsString('exists (select * from `shop_postage_countries` where `shop_orders`.`postage_country_id` = `shop_postage_countries`.`id`', $sql);
        $this->assertStringContainsString('and `postage_area_id` = 2)', $sql);
    }

    #[Test]
    public function theThreeWeekRuleFiltersOnTheAmericanAndOceanicPostageAreas(): void
    {
        $sql = $this->sqlFor('3 weeks');

        $this->assertStringContainsString('exists (select * from `shop_postage_countries` where `shop_orders`.`postage_country_id` = `shop_postage_countries`.`id`', $sql);
        $this->assertStringContainsString('and `postage_area_id` in (3, 4))', $sql);
    }

    protected function rule(string $notificationDelayText): ReviewInvitationRule
    {
        /** @var ReviewInvitationRule */
        return SendingRules::get()->firstWhere('notificationDelayText', $notificationDelayText);
    }

    protected function sqlFor(string $notificationDelayText): string
    {
        $rule = $this->rule($notificationDelayText);

        return ShopOrder::query()
            ->pipe(fn (Builder $query) => call_user_func($rule->pipe, $query))
            ->toRawSql();
    }
}
