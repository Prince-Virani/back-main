<?php

require 'vendor/autoload.php';

use Google\AdsApi\Common\OAuth2TokenBuilder;
use Google\AdsApi\AdManager\AdManagerSessionBuilder;
use Google\AdsApi\AdManager\v202502\ServiceFactory;
use Google\AdsApi\AdManager\Util\v202502\StatementBuilder;

header('Content-Type: application/json');

// Accept creative ID from CLI or web
$creativeId = isset($argv[1]) ? (int)$argv[1] : (isset($_GET['id']) ? (int)$_GET['id'] : null);

if (!$creativeId) {
    http_response_code(400);
    echo json_encode(['error' => 'Please pass creative ID as CLI argument or ?id=CREATIVE_ID']);
    exit;
}

try {
    // Step 1: Auth + session
    $oAuth2Credential = (new OAuth2TokenBuilder())->fromFile()->build();

    $session = (new AdManagerSessionBuilder())
        ->fromFile()
        ->withOAuth2Credential($oAuth2Credential)
        ->build();

    $serviceFactory = new ServiceFactory();
    $creativeService = $serviceFactory->createCreativeService($session);

    // Step 2: Build statement
    $statementBuilder = (new StatementBuilder())
        ->where('id = :id')
        ->limit(1)
        ->withBindVariableValue('id', $creativeId);

    $response = $creativeService->getCreativesByStatement($statementBuilder->toStatement());
    $creatives = $response->getResults();

    if (!$creatives || count($creatives) === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Creative not found']);
        exit;
    }

    // Step 3: Extract all creative properties using reflection
    function extractCreative($creative) {
        $reflect = new ReflectionClass($creative);
        $props = $reflect->getProperties();
        $result = [];

        foreach ($props as $prop) {
            $prop->setAccessible(true);
            $value = $prop->getValue($creative);

            if (is_object($value)) {
                $result[$prop->getName()] = extractCreative($value);
            } elseif (is_array($value)) {
                $result[$prop->getName()] = array_map(function ($item) {
                    return is_object($item) ? extractCreative($item) : $item;
                }, $value);
            } else {
                $result[$prop->getName()] = $value;
            }
        }

        return $result;
    }

    // Step 4: Output full creative JSON
    $creativeData = array_map('extractCreative', $creatives);
    echo json_encode($creativeData, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
