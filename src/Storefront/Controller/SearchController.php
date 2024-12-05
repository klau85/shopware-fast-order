<?php

namespace ShopwareFastOrder\Storefront\Controller;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Routing\RoutingException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class SearchController extends StorefrontController
{
    public function __construct(
        private readonly EntityRepository $productRepository,
    )
    {
    }

    #[Route(
        path: '/fast-order/search',
        name: 'frontend.fast.order.search',
        defaults: ['XmlHttpRequest' => true, '_httpCache' => true],
        methods: ['GET']
    )]
    public function search(Request $request, SalesChannelContext $context): Response
    {
        $productNumber = $request->query->get('productNumber');
        if (!$productNumber) {
            throw RoutingException::missingRequestParameter('productNumber');
        }

        $criteria = new Criteria();
        $criteria->addFilter(new ContainsFilter('productNumber', $productNumber));
        $criteria->addFilter(new EqualsFilter('active', true));

        $products = $this->productRepository->search($criteria, $context->getContext());

        return $this->renderStorefront('@SwFastOrder/storefront/layout/search.html.twig', ['products' => $products]);
    }
}