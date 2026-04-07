<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Google\AdsApi\AdManager\AdManagerSessionBuilder;
use Google\AdsApi\AdManager\Util\v202502\StatementBuilder;
use Google\AdsApi\AdManager\v202502\ServiceFactory;
use Google\AdsApi\Common\OAuth2TokenBuilder;

class AdUnitDetailController extends Controller
{
    public function show($adUnitId)
    {
        try {
            // OAuth & session setup
            $oAuth2Credential = (new OAuth2TokenBuilder())
                ->fromFile()
                ->build();

            $session = (new AdManagerSessionBuilder())
                ->fromFile()
                ->withOAuth2Credential($oAuth2Credential)
                ->build();

            $serviceFactory = new ServiceFactory();
            $inventoryService = $serviceFactory->createInventoryService($session);

            // Build the query
            $statementBuilder = (new StatementBuilder())
                ->where('id = :adUnitId')
                ->withBindVariableValue('adUnitId', $adUnitId)
                ->limit(StatementBuilder::SUGGESTED_PAGE_LIMIT);

            $page = $inventoryService->getAdUnitsByStatement($statementBuilder->toStatement());

            if ($page->getResults() === null) {
                return response()->json(['adunit' => null], 404);
            }

            $adUnit = $page->getResults()[0];

            $adUnitSizes = array_map(function ($size) {
                return [
                    'width' => $size->getSize()->getWidth(),
                    'height' => $size->getSize()->getHeight(),
                    'isAspectRatio' => $size->getSize()->getIsAspectRatio(),
                    'environmentType' => $size->getEnvironmentType(),
                ];
            }, $adUnit->getAdUnitSizes() ?? []);

            return response()->json([
                'adunit' => [
                    'id' => $adUnit->getId(),
                    'name' => $adUnit->getName(),
                    'code' => $adUnit->getAdUnitCode(),
                    'status' => $adUnit->getStatus(),
                    'adUnitSizes' => $adUnitSizes
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch ad unit details.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
