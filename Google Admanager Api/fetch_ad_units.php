<?php
// fetch_raw_results.php

require __DIR__ . '/vendor/autoload.php';

use Google\AdsApi\Common\OAuth2TokenBuilder;
use Google\AdsApi\AdManager\AdManagerSessionBuilder;
use Google\AdsApi\AdManager\v202502\ServiceFactory;
use Google\AdsApi\AdManager\Util\v202502\StatementBuilder;

try {
    // 1. Read optional 'id' parameter (from GET or CLI argv)
    $unitId = null;
    if (php_sapi_name() === 'cli' && isset($argv[1])) {
        $unitId = (int)$argv[1];
    } elseif (isset($_GET['id'])) {
        $unitId = (int)$_GET['id'];
    }

    // 2. Build OAuth2 credential & session
    $oAuth2  = (new OAuth2TokenBuilder())->fromFile()->build();
    $session = (new AdManagerSessionBuilder())
        ->fromFile()
        ->withOAuth2Credential($oAuth2)
        ->build();

    // 3. Create services
    $factory          = new ServiceFactory();
    $networkService   = $factory->createNetworkService($session);
    $inventoryService = $factory->createInventoryService($session);

    // 4. If no specific ID, get rootAdUnitId to fetch children
    if ($unitId === null) {
        $rootAdUnitId = $networkService->getCurrentNetwork()
                                       ->getEffectiveRootAdUnitId();
    }

    // 5. Build StatementBuilder
    $sb = new StatementBuilder();
    if ($unitId !== null) {
        // Fetch only the ad unit with the given ID
        $sb->where('id = :id')
           ->withBindVariableValue('id', $unitId);
    } else {
        // Fetch first 10 child units under root for debug
        $sb->where('parentId = :parentId')
           ->withBindVariableValue('parentId', $rootAdUnitId)
           ->limit(10);
    }

    // 6. Execute query
    $page    = $inventoryService->getAdUnitsByStatement($sb->toStatement());
    $results = $page->getResults();

    // 7. Dump raw results
    header('Content-Type: text/plain');
    if ($results === null) {
        echo "No ad units found.\n";
    } else {
        foreach ($results as $au) {
            print_r($au);
            echo "\n-----------------------------\n";
        }
    }

} catch (\Exception $e) {
    http_response_code(500);
    header('Content-Type: text/plain');
    echo "Error: " . $e->getMessage() . "\n";
}
