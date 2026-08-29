<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Models\Shop\ShopOrder;
use Exception;

class OrderAlreadyPaidException extends Exception
{
    public function __construct(public ShopOrder $order)
    {
        parent::__construct('Order has already been paid for');
    }
}
