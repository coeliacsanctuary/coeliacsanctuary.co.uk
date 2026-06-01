<?php

declare(strict_types=1);

namespace App\Services\GoogleMerchant;

use Google\Shopping\Merchant\Products\V1\Client\ProductInputsServiceClient;
use Google\Shopping\Merchant\Products\V1\DeleteProductInputRequest;
use Google\Shopping\Merchant\Products\V1\InsertProductInputRequest;
use Google\Shopping\Merchant\Products\V1\ProductInput;
use Google\Shopping\Merchant\Products\V1\UpdateProductInputRequest;

class GoogleMerchantProductManager
{
    protected ?ProductInputsServiceClient $serviceClient = null;

    public function __construct(protected GoogleMerchantClient $client)
    {
    }

    public function insert(ProductInput $productInput): ProductInput
    {
        $request = (new InsertProductInputRequest())
            ->setParent("accounts/{$this->client->merchantId()}")
            ->setProductInput($productInput)
            ->setDataSource($this->client->dataSource());

        return $this->service()->insertProductInput($request);
    }

    public function update(string $productInputName, ProductInput $productInput): ProductInput
    {
        $productInput->setName($productInputName);

        $request = (new UpdateProductInputRequest())
            ->setProductInput($productInput)
            ->setDataSource($this->client->dataSource());

        return $this->service()->updateProductInput($request);
    }

    public function delete(string $productInputName): void
    {
        $request = (new DeleteProductInputRequest())
            ->setName($productInputName)
            ->setDataSource($this->client->dataSource());

        $this->service()->deleteProductInput($request);
    }

    public function isEnabled(): bool
    {
        return $this->client->isEnabled();
    }

    protected function service(): ProductInputsServiceClient
    {
        return $this->serviceClient ??= new ProductInputsServiceClient([
            'credentials' => $this->client->client(),
            'transport' => 'rest',
        ]);
    }
}
