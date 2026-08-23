<?php

declare(strict_types=1);

namespace App\Actions\Shop;

use App\Models\Shop\ShopDiscountCode;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Encryption\Encrypter;

class ResolveDiscountForOrderAction
{
    public function __construct(
        protected Encrypter $encrypter,
        protected ApplyDiscountCodeAction $applyDiscountCodeAction,
    ) {
        //
    }

    /** @return array{0: ShopDiscountCode|null, 1: int|null} */
    public function handle(string $encryptedCode, string $basketToken): array
    {
        try {
            $code = $this->encrypter->decrypt($encryptedCode);

            $discountCode = ShopDiscountCode::query()->where('code', $code)->firstOrFail();
        } catch (DecryptException | ModelNotFoundException) {
            return [null, null];
        }

        return [$discountCode, $this->applyDiscountCodeAction->handle($discountCode, $basketToken)];
    }
}
