<?php declare(strict_types=1);

namespace Webkul\MPHyperlocal\Controller\Backend;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"api"})
 */
class HyperlocalBackendController extends StorefrontController
{
    /**
     * @Route("/api/{version}/wk.marketplace/add/seller/location", name="wk.marketplace.add.seller.location", methods={"POST"})
     */
    public function addSellerLocation(Request $request)
    {
        $address = $request->request->get('data')['location'];
        $customerId = $request->request->get('data')['customerId'];
        $storefrontHelperService = $this->container->get('storefront.helper');
        $googleMapApiKey = $storefrontHelperService->getSystemConfigurationValue('WebkulMPHyperlocal.config.googleMapApiKey');
        $url = "https://maps.google.com/maps/api/geocode/json?address=".urlencode($address)."&key=".$googleMapApiKey;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);    
        $responseJson = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($responseJson);

        if ($response->status == 'OK') {
            $latitude = $response->results[0]->geometry->location->lat;
            $longitude = $response->results[0]->geometry->location->lng;
            $data = [
                'customerId'=>$customerId,
                'location' => $address,
                'longitude' => $longitude,
                'latitude' => $latitude
            ];
            $this->container->get('marketplace_hyperlocal_shipping_location.repository')->create([$data],Context::createDefaultContext());
            return new JsonResponse(true);
        } else {
            return new JsonResponse(false);
        }
    }
    public function get_lat_long($address){
    
        $address = str_replace(" ", "+", $address);
    
        $json = file_get_contents("http://maps.google.com/maps/api/geocode/json?address=$address&sensor=false");
        $json = json_decode($json);
    
        $lat = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};
        $long = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};
        return $lat.','.$long;
    }
}