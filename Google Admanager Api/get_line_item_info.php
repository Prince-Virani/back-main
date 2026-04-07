<?php

require __DIR__ . '/vendor/autoload.php';

use Google\AdsApi\AdManager\AdManagerSessionBuilder;
use Google\AdsApi\Common\OAuth2TokenBuilder;
use Google\AdsApi\AdManager\Util\v202502\StatementBuilder;
use Google\AdsApi\AdManager\v202502\ServiceFactory;

// === CONFIGURE ===
$lineItemId = 7000946546; // ✅ Make sure this ID is valid

header('Content-Type: application/json');

// === AUTH & SESSION ===
$oauth2 = (new OAuth2TokenBuilder())->fromFile()->build();
$session = (new AdManagerSessionBuilder())
    ->fromFile()
    ->withOAuth2Credential($oauth2)
    ->build();

$factory = new ServiceFactory();
$lineItemService = $factory->createLineItemService($session);

// === BUILD STATEMENT ===
$statement = (new StatementBuilder())
    ->where('id = :id')
    ->withBindVariableValue('id', $lineItemId)
    ->limit(1);

try {
    $response = $lineItemService->getLineItemsByStatement($statement->toStatement());
    $lineItems = $response->getResults();

    if (!empty($lineItems)) {
        $lineItem = $lineItems[0];
        echo json_encode(extractObject($lineItem), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    } else {
        echo json_encode(['error' => "❌ No Line Item found with ID: $lineItemId"]);
    }
} catch (Exception $e) {
    echo json_encode(['error' => "❌ Error: " . $e->getMessage()]);
}

// === Recursive Deep Extractor ===
function extractObject($object) {
    $reflect = new ReflectionClass($object);
    $props = $reflect->getProperties();
    $result = [];

    foreach ($props as $prop) {
        $prop->setAccessible(true);
        $value = $prop->getValue($object);

        if (is_object($value)) {
            $result[$prop->getName()] = extractObject($value);
        } elseif (is_array($value)) {
            $result[$prop->getName()] = array_map(function ($item) {
                return is_object($item) ? extractObject($item) : $item;
            }, $value);
        } else {
            $result[$prop->getName()] = $value;
        }
    }

    return $result;
}
