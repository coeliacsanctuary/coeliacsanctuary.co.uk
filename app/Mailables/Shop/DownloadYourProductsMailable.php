<?php

declare(strict_types=1);

namespace App\Mailables\Shop;

use App\Infrastructure\MjmlMessage;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderDownloadLink;

class DownloadYourProductsMailable extends BaseShopMailable
{
    public function __construct(protected ShopOrderDownloadLink $downloadLink, protected string $key)
    {
        /** @var ShopOrder $order */
        $order = $this->downloadLink->order;

        parent::__construct($order, $key);
    }

    protected function generateLink(): string
    {
        return 'foobar';
    }

    public function toMail(): MjmlMessage
    {
        return MjmlMessage::make()
            ->subject('Your Coeliac Sanctuary digital downloads are ready!')
            ->mjml('mailables.mjml.shop.download-your-products', $this->baseData([
                'reason' => 'so you can download your digital products purchased through Coeliac Sanctuary Shop.',
                'downloadLink' => $this->generateLink(),
            ]));
    }
}
