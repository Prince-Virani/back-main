<?php

require 'vendor/autoload.php';

use Google\AdsApi\Common\OAuth2TokenBuilder;
use Google\AdsApi\AdManager\AdManagerSessionBuilder;
use Google\AdsApi\AdManager\v202502\ServiceFactory;
use Google\AdsApi\AdManager\v202502\AdExchangeCreative;
use Google\AdsApi\AdManager\v202502\Size;

// Replace this with your Advertiser ID
const ADVERTISER_ID = 5754591298;

try {
    // Auth and session
    $oAuth2Credential = (new OAuth2TokenBuilder())
        ->fromFile()
        ->build();

    $session = (new AdManagerSessionBuilder())
        ->fromFile()
        ->withOAuth2Credential($oAuth2Credential)
        ->build();

    $serviceFactory = new ServiceFactory();
    $creativeService = $serviceFactory->createCreativeService($session);

    // Define dummy size required for all requested sizes
    $size = new Size();
    $size->setWidth(1);
    $size->setHeight(1);
    $size->setIsAspectRatio(false); // Required for all-requested-sizes

    // Create creative
    $creative = new AdExchangeCreative();
    $creative->setAdvertiserId(ADVERTISER_ID);
    $creative->setName('Auto-generated via API with all sizes Final');
    $creative->setSize($size);
    $creative->setIsAllowsAllRequestedSizes(true);
    $creative->setAdBadgingEnabled(true);
    $creative->setIsInterstitial(false);
    $creative->setIsNativeEligible(false);
    $creative->setCodeSnippet('<div style="display:none">AdX auto creative</div>');

    $result = $creativeService->createCreatives([$creative]);

    foreach ($result as $createdCreative) {
        echo "✅ Creative Created: ID = {$createdCreative->getId()}, Name = {$createdCreative->getName()}\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
