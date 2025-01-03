<?php declare(strict_types=1);

namespace ShopwareFastOrder\Storefront\Controller;

use Shopware\Core\Checkout\Cart\CartException;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItemFactoryRegistry;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Validation\DataValidationDefinition;
use Shopware\Core\Framework\Validation\DataValidator;
use Shopware\Core\Framework\Validation\Exception\ConstraintViolationException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use ShopwareFastOrder\Validator\ProductNumberExists;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class FastOrderController extends StorefrontController
{
    public const DEFAULT_ROWS_COUNT = 4;

    public function __construct(
        private readonly LineItemFactoryRegistry $lineItemFactoryRegistry,
        private readonly CartService $cartService,
        private readonly EntityRepository $productRepository,
        private readonly DataValidator $validator,
    )
    {
    }

    #[Route(
        path: '/fast-order',
        name: 'frontend.fast.order',
        methods: ['GET', 'POST']
    )]
    public function index(Request $request, SalesChannelContext $context): Response
    {
        if ($request->getMethod() === 'GET') {
            return $this->renderStorefront('@SwFastOrder/storefront/page/form.html.twig', ["rowsCount" => self::DEFAULT_ROWS_COUNT]);
        }

        $productNumbersMap = $this->getProductNumbersMap($request);
        $quantitiesMap = $this->getQuantitiesMap($request);

        try {
            $criteria = new Criteria();
            $criteria->addFilter(new EqualsAnyFilter('productNumber', $productNumbersMap));
            $criteria->addFilter(new EqualsFilter('active', true));

            $products = $this->productRepository->search($criteria, $context->getContext());
            $this->validateSearchResults($products, $request);
            $this->addItemsToCart($products, $context, $productNumbersMap, $quantitiesMap);

            return $this->redirectToRoute('frontend.checkout.cart.page');
        } catch (ConstraintViolationException $formViolations) {
            return $this->renderStorefront('@SwFastOrder/storefront/page/form.html.twig', ['formViolations' => $formViolations, 'rowsCount' => count($productNumbersMap)]);
        }
    }

    private function getProductNumbersMap(Request $request): array
    {
        $productNumbers = [];
        foreach ($request->request->all() as $key => $value) {
            if (!str_starts_with($key, 'productNumber_') ) {
                continue;
            }

            $index = explode('_', $key)[1];
            $productNumbers[$index] = $value;
        }

        return $productNumbers;
    }

    private function getQuantitiesMap(Request $request): array
    {
        $quantities = [];
        foreach ($request->request->all() as $key => $value) {
            if (!str_starts_with($key, 'quantity_') ) {
                continue;
            }

            $index = explode('_', $key)[1];
            $quantities[$index] = (int)$value;
        }

        return $quantities;
    }

    private function validateSearchResults(EntitySearchResult $products, Request $request): void
    {
        $definition = new DataValidationDefinition('storefront.fast_order.search_results');

        foreach ($request->request->all() as $key => $item) {
            if (str_starts_with($key, 'productNumber_') ) {
                $definition->add($key, new NotBlank(), new ProductNumberExists($products));
            }

            if (str_starts_with($key, 'quantity_') ) {
                $index = explode('_', $key)[1];
                $productNumber = $request->request->get('productNumber_'.$index);
                $product = $products->filterByProperty('productNumber', $productNumber)->first();
                if (!$product) {
                    continue;
                }
                $definition->add($key, new NotBlank(), new Range(['min' => 1, 'max' => $product->getAvailableStock()]));
            }
        }

        $violations = $this->validator->getViolations($request->request->all(), $definition);

        if (!$violations->count()) {
            return;
        }

        throw new ConstraintViolationException($violations, $request->request->all());
    }

    private function addItemsToCart(
        EntitySearchResult $products,
        SalesChannelContext $context,
        array $productNumbersMap,
        array $quantitiesMap
    ): void
    {
        $items = [];
        foreach ($productNumbersMap as $index => $productNumber) {
            $product = $products->filterByProperty('productNumber', $productNumber)->first();
            $lineItemArray = [
                'id' => $product->getId(),
                'quantity' => $quantitiesMap[$index],
                'stackable' => true,
                'removable' => true,
                'type' => LineItem::PRODUCT_LINE_ITEM_TYPE,
            ];
            $item = $this->lineItemFactoryRegistry->create($lineItemArray, $context);
            $items[] = $item;
        }
        $cart = $this->cartService->getCart($context->getToken(), $context);
        $this->cartService->add($cart, $items, $context);
    }
}
