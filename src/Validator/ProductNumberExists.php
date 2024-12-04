<?php

namespace ShopwareFastOrder\Validator;

use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Symfony\Component\Validator\Constraint;
class ProductNumberExists extends Constraint
{
    public string $message = 'The product number "{{ productNumber }}" was not found.';
    public string $mode = 'strict';
    public EntitySearchResult $products;

    final public const PRODUCT_NUMBER_NOT_FOUND = '12d30fe7-feCf-411e-ac9b-1bfd5c900001';

    protected const ERROR_NAMES = [
        self::PRODUCT_NUMBER_NOT_FOUND => 'PRODUCT_NUMBER_NOT_FOUND',
    ];

    // all configurable options must be passed to the constructor
    public function __construct(
        EntitySearchResult $products,
        ?string $mode = null,
        ?string $message = null,
        ?array $groups = null,
        $payload = null)
    {
        parent::__construct([], $groups, $payload);

        $this->mode = $mode ?? $this->mode;
        $this->message = $message ?? $this->message;
        $this->products = $products;
    }

    public function validatedBy(): string
    {
        return static::class.'Validator';
    }
}