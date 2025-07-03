<?php

declare(strict_types=1);

namespace Jpeters8889\ShopGenerateResendSlipButton;

use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\SupportsDependentFields;

class ShopGenerateResendSlipButton extends Field
{
    use SupportsDependentFields;

    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'shop-generate-resend-slip-button';

    public function withOptions(array $options): self
    {
        return $this->withMeta(['options' => $options]);
    }

    public function orderId(int $id): self
    {
        return $this->withMeta(['orderId' => $id]);
    }
}
