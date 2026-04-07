<?php
require __DIR__ . '/vendor/autoload.php';

use Google\AdsApi\AdManager\AdManagerSessionBuilder;
use Google\AdsApi\Common\OAuth2TokenBuilder;
use Google\AdsApi\AdManager\v202502\ServiceFactory;
use Google\AdsApi\AdManager\v202502\CustomTargetingValue;
use Google\AdsApi\AdManager\v202502\CustomTargetingValueMatchType;

// OAuth2 & Session
$oAuth2Credential = (new OAuth2TokenBuilder())
    ->fromFile()
    ->build();

$session = (new AdManagerSessionBuilder())
    ->fromFile()
    ->withOAuth2Credential($oAuth2Credential)
    ->build();

$serviceFactory = new ServiceFactory();
$customTargetingService = $serviceFactory->createCustomTargetingService($session);

// Custom key ID for `page_url`
$customTargetingKeyId = 16138829;

// Values to add
$valueNames = [
    'travel.topsocialkhabar.com_112',
    'travel.topsocialkhabar.com_39',
    'travel.topsocialkhabar.com_36',
    'travel.topsocialkhabar.com_37',
    'travel.topsocialkhabar.com_41',
    'travel.topsocialkhabar.com_40',
    'travel.topsocialkhabar.com_43',
    'travel.topsocialkhabar.com_42',
    'travel.topsocialkhabar.com_109',
];

// Build CustomTargetingValue objects
$customValues = [];

foreach ($valueNames as $name) {
    $value = new CustomTargetingValue();
    $value->setCustomTargetingKeyId($customTargetingKeyId);
    $value->setName($name);
    //$value->setDisplayName($name);
    $value->setMatchType(CustomTargetingValueMatchType::EXACT);
    $customValues[] = $value;
}

// Create in Google Ad Manager
$result = $customTargetingService->createCustomTargetingValues($customValues);

foreach ($result as $val) {
    printf("✅ Created value: ID %d — Name: %s\n", $val->getId(), $val->getName());
}
