<?php

declare(strict_types=1);

namespace Tests\Concerns;

use Illuminate\Support\Str;
use Stripe\BalanceTransaction;
use Stripe\Card;
use Stripe\Charge;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\Refund;
use Stripe\Service\ChargeService;
use Stripe\Service\PaymentIntentService;
use Stripe\Service\RefundService;
use Stripe\StripeClient;
use Tests\TestCase;

/** @mixin TestCase */
trait MocksStripe
{
    protected ?StripeClient $stripeClient = null;

    protected function getStripeClient(): StripeClient
    {
        if ( ! $this->stripeClient) {
            $this->stripeClient = $this->partialMock(StripeClient::class);
        }

        return $this->stripeClient;
    }

    protected function mockCreatePaymentIntent(int $amount): string
    {
        $id = Str::password(12);
        $token = Str::uuid()->toString();

        $paymentIntent = $this->partialMock(PaymentIntentService::class);

        $this->getStripeClient()->paymentIntents = $paymentIntent;

        $paymentIntent->shouldReceive('create')
            ->withArgs(function (array $args) use ($amount) {
                $this->assertArrayHasKey('amount', $args);
                $this->assertEquals($amount, $args['amount']);

                return true;
            })
            ->andReturn(PaymentIntent::constructFrom([
                'id' => $id,
                'client_secret' => $token,
            ]))
            ->once();

        return $token;
    }

    protected function mockRetrievePaymentIntent(string $token, string $status = PaymentIntent::STATUS_SUCCEEDED, array $params = []): void
    {
        $paymentIntent = $this->partialMock(PaymentIntentService::class);

        $this->getStripeClient()->paymentIntents = $paymentIntent;

        $paymentIntent->shouldReceive('retrieve')
            ->andReturn($this->createPaymentIntent([
                'client_secret' => $token,
                'status' => $status,
                ...$params,
            ]))
            ->once();
    }

    protected function mockUpdatePaymentIntent(string $id): void
    {
        $paymentIntent = $this->partialMock(PaymentIntentService::class);

        $this->getStripeClient()->paymentIntents = $paymentIntent;

        $paymentIntent->shouldReceive('update')
            ->withArgs(function ($argId) use ($id) {
                $this->assertEquals($id, $argId);

                return true;
            })
            ->once();
    }

    protected function mockRetrieveCharge(string $chargeId): void
    {
        $charges = $this->partialMock(ChargeService::class);

        $charges->shouldReceive('retrieve')
            ->withArgs(fn ($id) => $chargeId === $id)
            ->andReturn($this->createCharge($chargeId))
            ->once();

        $this->getStripeClient()->charges = $charges;
    }

    protected function mockCreateRefund(string $chargeId, int $amount): Refund
    {
        $id = Str::password(12);
        $token = Str::uuid()->toString();

        $refunds = $this->partialMock(RefundService::class);

        $this->getStripeClient()->refunds = $refunds;

        $refundResponse = Refund::constructFrom([
            'id' => $id,
            'amount' => $amount,
        ]);

        $refunds->shouldReceive('create')
            ->withArgs(function (array $args) use ($chargeId, $amount) {
                $this->assertArrayHasKeys(['charge_id', 'amount'], $args);
                $this->assertEquals($chargeId, $args['charge_id']);
                $this->assertEquals($amount, $args['amount']);

                return true;
            })
            ->andReturn($refundResponse)
            ->once();

        return $refundResponse;
    }

    protected function createCharge(?string $id = null, int $fee = 100): Charge
    {
        return Charge::constructFrom([
            'id' => $id ?: Str::uuid()->toString(),
            'payment_method_details' => PaymentMethod::constructFrom([
                'card' => Card::constructFrom([
                    'last4' => '4242',
                    'exp_month' => '04',
                    'exp_year' => '24',
                ]),
                'type' => 'Card',
                'wallet' => [
                    'type' => 'Card',
                ],
            ]),
            'balance_transaction' => BalanceTransaction::constructFrom([
                'fee' => $fee,
            ]),
        ]);
    }

    protected function createPaymentIntent(array $params = []): PaymentIntent
    {
        return PaymentIntent::constructFrom(array_merge([

        ], $params));
    }
}
