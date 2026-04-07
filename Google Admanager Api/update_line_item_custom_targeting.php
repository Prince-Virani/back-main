<?php

require __DIR__ . '/vendor/autoload.php';

use Google\AdsApi\Common\OAuth2TokenBuilder;
use Google\AdsApi\AdManager\AdManagerSessionBuilder;
use Google\AdsApi\AdManager\v202502\ServiceFactory;
use Google\AdsApi\AdManager\v202502\CustomCriteria;
use Google\AdsApi\AdManager\v202502\CustomCriteriaSet;
use Google\AdsApi\AdManager\v202502\CustomCriteriaComparisonOperator;
use Google\AdsApi\AdManager\v202502\CustomCriteriaSetLogicalOperator;
use Google\AdsApi\AdManager\v202502\Targeting;
use Google\AdsApi\AdManager\Util\v202502\StatementBuilder;

// Step 1: Auth
$oAuth2Credential = (new OAuth2TokenBuilder())->fromFile()->build();
$session = (new AdManagerSessionBuilder())->fromFile()->withOAuth2Credential($oAuth2Credential)->build();

$serviceFactory = new ServiceFactory();
$lineItemService = $serviceFactory->createLineItemService($session);
$customTargetingService = $serviceFactory->createCustomTargetingService($session);

// Step 2: Parameters
$lineItemId = 7000946546;
$keyId = 16138829; // << Your known custom targeting key ID
$valueNames = [
    'travel.topsocialkhabar.com_112'
];

// Step 3: Get value IDs for these names
$escapedNames = array_map(fn($n) => "'" . addslashes($n) . "'", $valueNames);
$inClause = implode(',', $escapedNames);
$valueStmt = (new StatementBuilder())
    ->where("customTargetingKeyId = :keyId AND name IN ($inClause)")
    ->withBindVariableValue('keyId', $keyId)
    ->limit(500);
$valuePage = $customTargetingService->getCustomTargetingValuesByStatement($valueStmt->toStatement());

$valueIds = [];
foreach ($valuePage->getResults() as $val) {
    $valueIds[] = $val->getId();
}
if (empty($valueIds)) {
    exit("❌ No matching values found for key ID $keyId.\n");
}

// Step 4: Build criteria
$criteria = new CustomCriteria();
$criteria->setKeyId($keyId);
$criteria->setValueIds($valueIds);
$criteria->setOperator(CustomCriteriaComparisonOperator::IS);

// Step 5: Wrap in criteria set
$criteriaSet = new CustomCriteriaSet();
$criteriaSet->setChildren([$criteria]);
$criteriaSet->setLogicalOperator(CustomCriteriaSetLogicalOperator::AND_VALUE);

// Step 6: Get line item
$lineStmt = (new StatementBuilder())
    ->where('id = :id')
    ->withBindVariableValue('id', $lineItemId)
    ->limit(1);
$lineItemPage = $lineItemService->getLineItemsByStatement($lineStmt->toStatement());

if (empty($lineItemPage->getResults())) {
    exit("❌ Line item ID $lineItemId not found.\n");
}
$lineItem = $lineItemPage->getResults()[0];

// Step 7: Apply targeting
$targeting = $lineItem->getTargeting() ?? new Targeting();
$targeting->setCustomTargeting($criteriaSet);
$lineItem->setTargeting($targeting);

// Step 8: Update line item
$updated = $lineItemService->updateLineItems([$lineItem]);

foreach ($updated as $item) {
    echo "✅ Line item ID {$item->getId()} updated with values:\n";
    foreach ($valueNames as $v) {
        echo "- $v\n";
    }
}
