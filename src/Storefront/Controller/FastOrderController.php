<?php declare(strict_types=1);

namespace ShopwareFastOrder\Storefront\Controller;

use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class FastOrderController extends StorefrontController
{
    #[Route(
        path: '/fast-order',
        name: 'frontend.fast.order',
        methods: ['GET']
    )]
    public function showForm(Request $request, SalesChannelContext $context): Response
    {
        return $this->renderStorefront('@SwFastOrder/storefront/page/form.html.twig', []);
    }

    #[Route(
        path: '/process-fast-order',
        name: 'frontend.process.fast.order',
        methods: ['POST']
    )]
    public function processFastOrder(Request $request, SalesChannelContext $context): Response
    {
        return $this->renderStorefront('@SwFastOrder/storefront/page/form.html.twig', []);
    }
}
