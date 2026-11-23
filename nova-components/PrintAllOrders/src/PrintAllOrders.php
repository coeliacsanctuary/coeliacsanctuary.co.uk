<?php

declare(strict_types=1);

namespace Jpeters8889\PrintAllOrders;

use Laravel\Nova\Card;

class PrintAllOrders extends Card
{
    /**
     * @var string
     */
    public $width = 'full';

    public $height = 'dynamic';

    /**
     * @return string
     */
    public function component()
    {
        return 'print-all-orders';
    }
}
