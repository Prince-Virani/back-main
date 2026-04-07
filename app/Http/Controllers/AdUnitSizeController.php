<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\AdsApi\AdManager\AdManagerSessionBuilder;
use Google\AdsApi\AdManager\Util\v202502\StatementBuilder;
use Google\AdsApi\AdManager\v202502\ServiceFactory;
use Google\AdsApi\AdManager\v202502\AdUnitSize;
use Google\AdsApi\Common\OAuth2TokenBuilder;

class AdUnitSizeController extends Controller
{
    public function getAllAdUnitSizes()
    {
        // Build OAuth credentials from adsapi_php.ini
        $oAuth2Credential = (new OAuth2TokenBuilder())
            ->fromFile() // Make sure adsapi_php.ini is configured!
            ->build();

        // Build the Ad Manager API session
        $session = (new AdManagerSessionBuilder())
            ->fromFile()
            ->withOAuth2Credential($oAuth2Credential)
            ->build();

        $serviceFactory = new ServiceFactory();
        $inventoryService = $serviceFactory->createInventoryService($session);

        // Create a statement to select all ad unit sizes
        $statementBuilder = new StatementBuilder();

        // Fetch ad unit sizes
        $adUnitSizes = $inventoryService->getAdUnitSizesByStatement(
            $statementBuilder->toStatement()
        );

        // Print adUnitSizes to see the full structure
        dd($adUnitSizes); // Dump and die to view the result

        // Check if ad unit sizes are returned as an array
        if (is_array($adUnitSizes)) {
            // Prepare the data to be returned
            $sizes = [];
            foreach ($adUnitSizes as $i => $adUnitSize) {
                $sizes[] = [
                    'id' => $adUnitSize->getId(),
                    'fullDisplayString' => $adUnitSize->getFullDisplayString(),
                    'width' => $adUnitSize->getSize()->getWidth(),
                    'height' => $adUnitSize->getSize()->getHeight(),
                ];
            }

            // Return the data as JSON
            return response()->json([
                'count' => count($adUnitSizes),
                'adUnitSizes' => $sizes,
            ]);
        } else {
            return response()->json([
                'message' => 'No ad unit sizes found.',
            ]);
        }
    }
}
