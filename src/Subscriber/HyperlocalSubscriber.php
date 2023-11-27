<?php declare(strict_types=1);

namespace Webkul\MPHyperlocal\Subscriber;

use Shopware\Core\Checkout\Cart\Order\IdStruct;
use Shopware\Core\Content\Product\Events\ProductListingCriteriaEvent;
use Shopware\Core\Content\Product\Events\ProductListingResultEvent;
use Shopware\Core\Content\Product\Events\ProductSearchCriteriaEvent;
use Shopware\Core\Content\Product\Events\ProductSuggestCriteriaEvent;
use Shopware\Core\Content\Product\Events\ProductSuggestResultEvent;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Storefront\Page\Checkout\Offcanvas\OffcanvasCartPageLoadedEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Shopware\Storefront\Pagelet\Footer\FooterPageletLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class HyperlocalSubscriber implements EventSubscriberInterface
{
    protected $container;
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    public static function getSubscribedEvents()
    {
        return [
            FooterPageletLoadedEvent::class => "hyperlocalLocation",
            ProductListingCriteriaEvent::class=> "productListingCriteria",
            ProductSuggestCriteriaEvent::class => "productSuggestCriteria",
            ProductSearchCriteriaEvent::class => "productSearchCriteria"
            
        ];
    }
    public function productSearchCriteria(ProductSearchCriteriaEvent $event){
        $storefrontHelper = $this->container->get('storefront.helper');
        $salesChannelId = $event->getSalesChannelContext()->getSalesChannel()->getId();
        $defaultProductConfig = $storefrontHelper->getSystemConfigurationValue('WebkulMPHyperlocal.config.defaultProducts',$salesChannelId);
        // for default products
        if($defaultProductConfig) {
                        
            $marketplaceProduct = $this->container->get('marketplace_product.repository')->search((new Criteria()),Context::createDefaultContext())->getElements();
            
            $marketplaceProductIds = [];
            foreach($marketplaceProduct as $mp) {
                
                array_push($marketplaceProductIds, $mp->get('productId'));
            }
           
            $swProductIds = [];
            $swProducts = $this->container->get('product.repository')->search((new Criteria())->addFilter(new NotFilter(
                NotFilter::CONNECTION_OR,[new EqualsAnyFilter('id',$marketplaceProductIds)]
            )),Context::createDefaultContext())->getElements();
            foreach($swProducts as $pro) {
                array_push($swProductIds,$pro->getId());
            }
            
        }
        $session = new Session();
        $productIds = $session->get('productIds');
        if(empty($productIds)){
            $productIds = [];
        }
        if(isset($swProductIds)) {
            
            $productIds = array_merge($productIds,$swProductIds);
            
        }
        $session = new Session();
        $productIds = $session->get('productIds');
        if(empty($productIds)){
            $productIds = [];
        }
        
        $event->getCriteria()->addFilter(new EqualsAnyFilter('id',$productIds));
    }
    
    public function productSuggestCriteria(ProductSuggestCriteriaEvent $event){
        $storefrontHelper = $this->container->get('storefront.helper');
        $salesChannelId = $event->getSalesChannelContext()->getSalesChannel()->getId();
        $defaultProductConfig = $storefrontHelper->getSystemConfigurationValue('WebkulMPHyperlocal.config.defaultProducts',$salesChannelId);
        // for default products
        if($defaultProductConfig) {
                        
            $marketplaceProduct = $this->container->get('marketplace_product.repository')->search((new Criteria()),Context::createDefaultContext())->getElements();
            
            $marketplaceProductIds = [];
            foreach($marketplaceProduct as $mp) {
                
                array_push($marketplaceProductIds, $mp->get('productId'));
            }
           
            $swProductIds = [];
            $swProducts = $this->container->get('product.repository')->search((new Criteria())->addFilter(new NotFilter(
                NotFilter::CONNECTION_OR,[new EqualsAnyFilter('id',$marketplaceProductIds)]
            )),Context::createDefaultContext())->getElements();
            foreach($swProducts as $pro) {
                array_push($swProductIds,$pro->getId());
            }
            
        }
        $session = new Session();
        $productIds = $session->get('productIds');
        if(empty($productIds)){
            $productIds = [];
        }
        if(isset($swProductIds)) {
            
            $productIds = array_merge($productIds,$swProductIds);
            
        }
        $session = new Session();
        $productIds = $session->get('productIds');
        if(empty($productIds)){
            $productIds = [];
        }
        
        $event->getCriteria()->addFilter(new EqualsAnyFilter('id',$productIds));
    }
   
    public function productListingCriteria(ProductListingCriteriaEvent $event)
    {
        $storefrontHelper = $this->container->get('storefront.helper');
        $salesChannelId = $event->getSalesChannelContext()->getSalesChannel()->getId();
        $defaultProductConfig = $storefrontHelper->getSystemConfigurationValue('WebkulMPHyperlocal.config.defaultProducts',$salesChannelId);
        
        // for default products
        if($defaultProductConfig) {
                        
            $marketplaceProduct = $this->container->get('marketplace_product.repository')->search((new Criteria()),Context::createDefaultContext())->getElements();
            
            $marketplaceProductIds = [];
            foreach($marketplaceProduct as $mp) {
                
                array_push($marketplaceProductIds, $mp->get('productId'));
            }
           
            $swProductIds = [];
            $swProducts = $this->container->get('product.repository')->search((new Criteria())->addFilter(new NotFilter(
                NotFilter::CONNECTION_OR,[new EqualsAnyFilter('id',$marketplaceProductIds)]
            )),Context::createDefaultContext())->getElements();
            foreach($swProducts as $pro) {
                array_push($swProductIds,$pro->getId());
            }
            
        }
        $session = new Session();
        $productIds = $session->get('productIds');
        if(empty($productIds)){
            $productIds = [];
        }
        if(isset($swProductIds)) {
            
            $productIds = array_merge($productIds,$swProductIds);
            
        }
        
        $event->getCriteria()->addFilter(new EqualsAnyFilter('id',$productIds));
    }
    public function hyperlocalLocation(FooterPageletLoadedEvent $event)
    {
        
        $session = new Session();
        $location = $session->get('location');
        $lat = $session->get('lat');
        $lng = $session->get('lng');
        $state =  $session->get('state');
        $state_code = $session->get('stateCode');
        $country = $session->get('country');
        $country_code = $session->get('countryCode');
        $productIds = $session->get('productIds');
        $adminProduct = $session->get('adminProduct');
        $sellerIds = $session->get('sellerIds');
        
        $storefrontHelperService = $this->container->get('storefront.helper');
        $salesChannelId = $event->getSalesChannelContext()->getSalesChannel()->getId();
        $listingType = $storefrontHelperService->getSystemConfigurationValue('WebkulMPHyperlocal.config.listingType',$salesChannelId);
        $googleMapApiKey = $storefrontHelperService->getSystemConfigurationValue('WebkulMPHyperlocal.config.googleMapApiKey',$salesChannelId);
        $popupTitle = $storefrontHelperService->getSystemConfigurationValue('WebkulMPHyperlocal.config.popupTitle',$salesChannelId);
        $serializedMarketplaceSellers = [];
        if($listingType == 'seller' && $sellerIds) {
            $marketplaceSellers = $storefrontHelperService->getMarketplaceSellers(array_unique($sellerIds));
            
    
            foreach ($marketplaceSellers as $marketplaceSeller) {
                
                if ($marketplaceSeller['isApproved'] && $marketplaceSeller['profileStatus']) {
                    if($marketplaceSeller->get('mediaLogo')){
                    $marketplaceSeller['storeUrl']= $marketplaceSeller->get('mediaLogo')->get('url');
                    }
                    array_push($serializedMarketplaceSellers, $marketplaceSeller->jsonSerialize());
                }
            }
            
            
        }
        
        $hyperlocalConfig = [
            'location'=>$location,
            'lat'=> $lat,
            'lng'=> $lng,
            'state'=> $state,
            'stateCode'=> $state_code,
            'country'=> $country,
            'countryCode'=> $country_code,
            'listingType'=> $listingType,
            'googleMapApiKey' => $googleMapApiKey,
            'popupTitle' => $popupTitle
        ];
        
        $extensions = [];

        if ($hyperlocalConfig) {
         foreach ($hyperlocalConfig as $key => $config) {
             if($config){

                 $extensions[$key] = new IdStruct((string)$config);
             }
         }
        }
        $event->getPagelet()->addExtensions($extensions);
        
    }

    
    
}