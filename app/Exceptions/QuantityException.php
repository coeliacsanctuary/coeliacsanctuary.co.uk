<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class QuantityException extends Exception
{
    public static function notEnoughAvailable(): self
    {
        return new self('Not enough quantity available');
    }
}
