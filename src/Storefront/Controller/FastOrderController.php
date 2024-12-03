<?php declare(strict_types=1);

namespace ShopwareFastOrder\Storefront\Controller;

use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItemFactoryRegistry;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\Routing\RoutingException;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class FastOrderController extends StorefrontController
{
    public function __construct(
        private readonly LineItemFactoryRegistry $lineItemFactoryRegistry,
        private readonly CartService $cartService,
        private readonly EntityRepository $productRepository,
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
        if ($request->getMethod() === 'POST') {
            $productNumbers = $request->request->all('productNumber');
            $criteria = new Criteria();
            $criteria->addFilter(new EqualsAnyFilter('productNumber', $productNumbers));

            $products = $this->productRepository->search($criteria, $context->getContext());
            $items = [];
            /** @var ProductEntity $product */
            foreach ($products->getElements() as $product) {
                $lineItemArray = [
                    'id' => $product->getId(),
                    'quantity' => 1,
                    'stackable' => true,
                    'removable' => true,
                    'type' => LineItem::PRODUCT_LINE_ITEM_TYPE,
                ];
                $item = $this->lineItemFactoryRegistry->create($lineItemArray, $context);
                $items[] = $item;
            }
            $cart = $this->cartService->getCart($context->getToken(), $context);
            $this->cartService->add($cart, $items, $context);

            return $this->redirectToRoute('frontend.checkout.cart.page');
        }

        return $this->renderStorefront('@SwFastOrder/storefront/page/form.html.twig', []);
    }
}
