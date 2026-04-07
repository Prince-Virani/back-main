<?php

require __DIR__ . '/vendor/autoload.php';

use Google\AdsApi\Common\OAuth2TokenBuilder;
use Google\AdsApi\AdManager\AdManagerSessionBuilder;
use Google\AdsApi\AdManager\Util\v202502\StatementBuilder;
use Google\AdsApi\AdManager\v202502\{
    AdExchangeCreative,
    AdUnitTargeting,
    CostType,
    CreativePlaceholder,
    CreativeRotationType,
    CustomCriteria,
    CustomCriteriaSet,
    CustomCriteriaComparisonOperator,
    CustomCriteriaSetLogicalOperator,
    Goal,
    GoalType,
    InventoryTargeting,
    LineItem,
    LineItemCreativeAssociation,
    LineItemType,
    Money,
    RequestPlatform,
    RequestPlatformTargeting,
    RoadblockingType,
    ServiceFactory,
    Size,
    SkippableAdType,
    StartDateTimeType,
    Targeting,
    UnitType,
    CustomTargetingValue,
    CustomTargetingValueMatchType
};

// === CONFIGURATION ===
$orderId = 3707499258;
$adUnitId = '23280246729';
$lineItemName = 'Line item with creative Ncik 2';
$webProperty = 'ca-pub-9159294888445385';
$advertiserId = 5754591298;
$customTargetingKeyId = 16125917;
$valueNames = ['travel.topsocialkhabar.com_113'];

try {
    // AUTH & SESSION
    $oauth2 = (new OAuth2TokenBuilder())->fromFile()->build();
    $session = (new AdManagerSessionBuilder())->fromFile()->withOAuth2Credential($oauth2)->build();

    $factory = new ServiceFactory();
    $lineItemService = $factory->createLineItemService($session);
    $creativeService = $factory->createCreativeService($session);
    $licaService = $factory->createLineItemCreativeAssociationService($session);
    $customTargetingService = $factory->createCustomTargetingService($session);

    // 1) FETCH EXISTING CUSTOM TARGETING VALUES BY NAME
    $escapedNames = array_map(fn($n) => "'" . addslashes($n) . "'", $valueNames);
    $inClause = implode(',', $escapedNames);

    $stmt = (new StatementBuilder())
        ->where("customTargetingKeyId = :keyId AND name IN ($inClause)")
        ->withBindVariableValue('keyId', $customTargetingKeyId)
        ->limit(500);

    $existingValuePage = $customTargetingService->getCustomTargetingValuesByStatement($stmt->toStatement());
    $existingValues = $existingValuePage->getResults() ?: [];

    $existingValueNames = [];
    $existingValueIds = [];
    foreach ($existingValues as $val) {
        $existingValueNames[] = $val->getName();
        $existingValueIds[] = $val->getId();
    }

    // 2) CREATE MISSING CUSTOM TARGETING VALUES
    $missingNames = array_diff($valueNames, $existingValueNames);
    $createdValueIds = [];
    if (!empty($missingNames)) {
        $toCreate = [];
        foreach ($missingNames as $name) {
            $value = new CustomTargetingValue();
            $value->setCustomTargetingKeyId($customTargetingKeyId);
            $value->setName($name);
            $value->setMatchType(CustomTargetingValueMatchType::EXACT);
            $toCreate[] = $value;
        }
        $createdValues = $customTargetingService->createCustomTargetingValues($toCreate);
        foreach ($createdValues as $val) {
            printf("✅ Created value: ID %d — Name: %s\n", $val->getId(), $val->getName());
            $createdValueIds[] = $val->getId();
        }
    }

    $allValueIds = array_merge($existingValueIds, $createdValueIds);
    if (empty($allValueIds)) {
        exit("❌ No custom targeting values available.\n");
    }

    // 3) CREATE LINE ITEM WITH BASIC TARGETING (NO CUSTOM TARGETING YET)
    $targeting = (new Targeting())
        ->setInventoryTargeting(
            (new InventoryTargeting())->setTargetedAdUnits([
                (new AdUnitTargeting())->setAdUnitId($adUnitId)->setIncludeDescendants(true)
            ])
        )
        ->setRequestPlatformTargeting(
            (new RequestPlatformTargeting())->setTargetedRequestPlatforms([RequestPlatform::BROWSER])
        );

    $lineItem = (new LineItem())
        ->setName($lineItemName)
        ->setOrderId($orderId)
        ->setLineItemType(LineItemType::AD_EXCHANGE)
        ->setAllowOverbook(false)
        ->setPriority(12)
        ->setAutoExtensionDays(0)
        ->setRoadblockingType(RoadblockingType::ONE_OR_MORE)
        ->setSkippableAdType(SkippableAdType::DISABLED)
        ->setTargeting($targeting)
        ->setCreativePlaceholders([
            (new CreativePlaceholder())->setSize(new Size(1, 1, false))->setCreativeSizeType('IGNORED')
        ])
        ->setCreativeRotationType(CreativeRotationType::EVEN)
        ->setDeliveryRateType('EVENLY')
        ->setStartDateTimeType(StartDateTimeType::IMMEDIATELY)
        ->setUnlimitedEndDateTime(true)
        ->setCostType(CostType::CPM)
        ->setCostPerUnit(new Money('INR', 0))
        ->setPrimaryGoal(
            (new Goal())->setUnits(-1)->setUnitType(UnitType::IMPRESSIONS)->setGoalType(GoalType::NONE)
        )
        ->setWebPropertyCode($webProperty);

    [$createdLineItem] = $lineItemService->createLineItems([$lineItem]);
    $lineItemId = $createdLineItem->getId();
    echo "✅ Line Item Created: ID = {$lineItemId}\n";

    // 4) CREATE Ad EXCHNAGE CREATIVE
    $now = new \DateTime('now', new \DateTimeZone('Asia/Kolkata'));
    $formattedTime = $now->format('d/m/Y, H:i:s');

    $size = new Size();
    $size->setWidth(1);
    $size->setHeight(1);
    $size->setIsAspectRatio(false);

    $creative = new AdExchangeCreative();
    $creative->setAdvertiserId($advertiserId);
    $creative->setName("{$lineItemName} – All requested sizes – {$formattedTime}");
    $creative->setSize($size);
    $creative->setIsAllowsAllRequestedSizes(true);
    $creative->setAdBadgingEnabled(true);
    $creative->setIsInterstitial(false);
    $creative->setIsNativeEligible(false);
    $creative->setCodeSnippet('<div style="display:none">AdX Stock Creative</div>');

    [$createdCreative] = $creativeService->createCreatives([$creative]);
    $creativeId = $createdCreative->getId();
    echo "✅ Creative Created: ID = {$creativeId}\n";

    // 5) ATTACH CREATIVE TO LINE ITEM
    $lica = new LineItemCreativeAssociation();
    $lica->setLineItemId($lineItemId);
    $lica->setCreativeId($creativeId);
    $licaService->createLineItemCreativeAssociations([$lica]);
    echo "✅ Creative ID {$creativeId} attached to Line Item ID {$lineItemId}\n";

    // 6) FETCH LINE ITEM AGAIN TO UPDATE TARGETING WITH CUSTOM CRITERIA
    $lineStmt = (new StatementBuilder())
        ->where('id = :id')
        ->withBindVariableValue('id', $lineItemId)
        ->limit(1);

    $lineItemPage = $lineItemService->getLineItemsByStatement($lineStmt->toStatement());
    if (empty($lineItemPage->getResults())) {
        exit("❌ Line item ID {$lineItemId} not found for update.\n");
    }
    $lineItem = $lineItemPage->getResults()[0];

    // 7) SET CUSTOM TARGETING CRITERIA WITH ALL VALUE IDS
    $criteria = new CustomCriteria();
    $criteria->setKeyId($customTargetingKeyId);
    $criteria->setValueIds($allValueIds);
    $criteria->setOperator(CustomCriteriaComparisonOperator::IS);

    $criteriaSet = new CustomCriteriaSet();
    $criteriaSet->setChildren([$criteria]);
    $criteriaSet->setLogicalOperator(CustomCriteriaSetLogicalOperator::AND_VALUE);

    $existingTargeting = $lineItem->getTargeting() ?? new Targeting();
    $existingTargeting->setCustomTargeting($criteriaSet);
    $lineItem->setTargeting($existingTargeting);

    // 8) UPDATE LINE ITEM WITH CUSTOM TARGETING
    $updatedLineItems = $lineItemService->updateLineItems([$lineItem]);

    foreach ($updatedLineItems as $updated) {
        echo "✅ Line item ID {$updated->getId()} updated with custom targeting values:\n";
        foreach ($valueNames as $name) {
            echo "- {$name}\n";
        }
    }

} catch (\Exception $e) {
    exit("❌ Error: " . $e->getMessage() . "\n");
}
