<?php

namespace ShopwareFastOrder\Validator;

use Shopware\Core\Content\Product\ProductEntity;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ProductNumberExistsValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ProductNumberExists) {
            throw new UnexpectedTypeException($constraint, ProductNumberExists::class);
        }

        // custom constraints should ignore null and empty values to allow
        // other constraints (NotBlank, NotNull, etc.) to take care of that
        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            // throw this exception if your validator cannot handle the passed type so that it can be marked as invalid
            throw new UnexpectedValueException($value, 'string');

            // separate multiple types using pipes
            // throw new UnexpectedValueException($value, 'string|int');
        }

        /** @var ProductEntity $product */
        foreach ($constraint->products->getElements() as $product) {
            if ($product->getProductNumber() === $value) {
                return;
            }
        }

        // the argument must be a string or an object implementing __toString()
        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ productNumber }}', $value)
            ->setCode(ProductNumberExists::PRODUCT_NUMBER_NOT_FOUND)
            ->addViolation();
    }
}