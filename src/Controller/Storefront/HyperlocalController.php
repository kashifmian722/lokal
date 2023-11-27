<?php declare(strict_types=1);

namespace Webkul\MPHyperlocal\Controller\Storefront;

use Shopware\Core\Framework\Adapter\Cache\CacheClearer;
use Enqueue\Container\NotFoundException;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Storefront\Page\Navigation\NavigationPageLoader;
use Symfony\Component\Routing\Annotation\Route;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * @RouteScope(scopes={"storefront"})
 */
class HyperlocalController extends StorefrontController
{
    /**
     * @var NavigationPageLoader
     */
    protected $navigationPageLoader;
    
    protected $cartService;
    /**
     * @var CacheClearer
     */
    private $cacheClearer;


    public function __construct(NavigationPageLoader $navigationPageLoader,CartService $cartService, CacheClearer $cacheClearer)
    {
        $this->navigationPageLoader = $navigationPageLoader;
        $this->cartService = $cartService;
        $this->cacheClearer = $cacheClearer;
    }

    /**
     * @Route("marketplace/shipping/location", name="marketplace.shipping.location", methods={"GET", "POST"}, defaults={"csrf_protected"=false})
     */
    
    public function renderShippingLocation(Request $request, SalesChannelContext $salesChannelContext) 
    {
        $this->marketplaceDenyAccessUnlessSellerLoggedIn($salesChannelContext);
        $data = $this->navigationPageLoader->load($request, $salesChannelContext);
        $shippingLLocationRepository = $this->container->get('marketplace_hyperlocal_shipping_location.repository');
        $customerId = $salesChannelContext->getCustomer()->getId();
        $criteria = (new Criteria())->addFilter(new EqualsFilter('customerId', $customerId));
        $sellerLocations = $shippingLLocationRepository->search($criteria, $salesChannelContext->getContext())->getElements();
       
        return $this->renderStorefront('@WebkulMPHyperlocal/storefront/seller-location.html.twig', [
            'page'=> $data,
            'sellerLocations' => $sellerLocations,
            'environment' => $this->container->getParameter('kernel.environment')
            
        ]);
    }
    
    /**
     * @Route("wk/mp/hyperlocal/location", name="wk.mp.hyperlocal.location", methods={"POST"}, defaults={"csrf_protected"=false, "XmlHttpRequest"=true})
     */
    public function addLocation(Request $request, SalesChannelContext $salesChannelContext)
    {
        $shippingLocationRepository = $this->container->get('marketplace_hyperlocal_shipping_location.repository');
        $customerId = $salesChannelContext->getCustomer()->getId();
        
        $data = [
            'customerId' => $customerId,
            'location' => $request->request->get('location'),
            'longitude' => $request->request->get('lng'),
            'latitude' => $request->request->get('lat'),
            'shippingOption' => $request->request->get('option')

        ];
        $shippingLocationRepository->create([$data], $salesChannelContext->getContext());
        $this->addFlash('success', 'Seller location added suceessfully!');
       return $this->redirectToRoute('marketplace.shipping.location');
       
    }
    
    /**
     * @Route("wk-mp/add/location", name="wk-mp.add.location", methods={"POST"}, defaults={"csrf_protected"=false,  "XmlHttpRequest"=true})
     */
    public function addCustomerLocation(Request $request,SalesChannelContext $salesChannelContext,Cart $cart) {
        $location = $request->request->get('location');
        $lat = (float)$request->request->get('lat');
        $lng = (float)$request->request->get('lng');
        $session = new Session();
        $this->cacheClearer->clear();
        $session->set('location', $location);
        $session->set('lat',$lat);
        $session->set('lng',$lng);
        $session->set('state',$request->request->get('state'));
        $session->set('stateCode',$request->request->get('state_code'));
        $session->set('country',$request->request->get('country'));
        $session->set('countryCode',$request->request->get('country_code'));
        $storefrontHelper = $this->container->get('storefront.helper');
        $salesChannelId = $salesChannelContext->getSalesChannel()->getId();
        $radius = $storefrontHelper->getSystemConfigurationValue('WebkulMPHyperlocal.config.radius',$salesChannelId);
        $radiusUnit = $storefrontHelper->getSystemConfigurationValue('WebkulMPHyperlocal.config.radiusUnit',$salesChannelId);
        $listingType = $storefrontHelper->getSystemConfigurationValue('WebkulMPHyperlocal.config.listingType',$salesChannelId);
        
        
        $repository = $this->container->get('marketplace_hyperlocal_shipping_location.repository');;
        $criteria = new Criteria();
        $sellersLocation = $repository->search($criteria,$salesChannelContext->getContext())->getElements();
        $sellerIds = [];
        if($sellersLocation) {
            foreach($sellersLocation as $value) {
                $dist =$this->getDistanceBetweenPointsNew($lat,$lng,$value['latitude'],$value['longitude'],$radiusUnit);
                if($dist < $radius) {
                    
                     $sellerId = $this->container->get('marketplace_seller.repository')->search((new Criteria())->addFilter(new EqualsFilter('customerId', $value['customerId'])), $salesChannelContext->getContext())->first()->getId();
                       array_push($sellerIds, $sellerId);
                } 
            }
            
        }
        $marketplaceProductRepository = $this->container->get('marketplace_product.repository');
        $criteria = (new Criteria())->addFilter(new EqualsAnyFilter('marketplaceSellerId', $sellerIds));
        $marketplaceProduct = $marketplaceProductRepository->search($criteria, $salesChannelContext->getContext())->getElements();
        $productIds = [];
        foreach($marketplaceProduct as $product) {
            array_push($productIds, $product->get('productId'));
        }
        $session->set('productIds',$productIds);
        $session->set('sellerIds',$sellerIds);
        
        // on change location cart will be empty
        
        $cartLineItems = $this->cartService->getCart($salesChannelContext->getToken(),$salesChannelContext)->getLineItems();
        foreach($cartLineItems as $cartLineItem) {
            if(!in_array($cartLineItem->getId(),$productIds)){
                $this->cartService->remove($cart,$cartLineItem->getId(),$salesChannelContext);
            }
        }
        
        return new JsonResponse(['status'=>true, 'listingType'=>$listingType]);
    }
    
    public function getDistanceBetweenPointsNew($latitude1, $longitude1, $latitude2, $longitude2, $unit = 'Mi') {
        $theta = $longitude1 - $longitude2; 
        $distance = (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2))) + (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta))); 
        $distance = acos($distance); 
        $distance = rad2deg($distance); 
        $distance = $distance * 60 * 1.1515; 
        switch($unit) { 
          case 'Mi': 
            break; 
          case 'Km' : 
            $distance = $distance * 1.609344; 
        } 
        return (round($distance,2)); 
      }
    /**
     * @Route("/marketplace/hyperlocal/delete/location", name="wk-mp.delete.location", methods={"DELETE"}, defaults={"csrf_protected"=false})
     */
    public function deleteSellerLocation(Request $request,SalesChannelContext $salesChannelContext)
    {
        $locationId = $request->request->get('locationId');
        $this->container->get('marketplace_hyperlocal_shipping_location.repository')->delete([['id'=>$locationId]],$salesChannelContext->getContext());
        $this->addFlash('success', 'Seller location deleted suceessfully!');
        return new JsonResponse(true);
    }
    public function marketplaceDenyAccessUnlessSellerLoggedIn(SalesChannelContext $salesChannelContext)
    {
        $this->denyAccessUnlessLoggedIn();

        $marketplaceSellerRepository = $this->container->get('marketplace_seller.repository');

        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('customerId', $salesChannelContext->getCustomer()->getId()));

        $seller = $marketplaceSellerRepository->search($criteria, Context::createDefaultContext())
            ->getEntities()
            ->getElements();

        if (!$seller[array_keys($seller)[0]]->get('isApproved')) {
            throw new NotFoundException("Page not found");
        }
    }
}