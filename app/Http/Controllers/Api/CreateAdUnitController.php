<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Google\AdsApi\Common\OAuth2TokenBuilder;
use Google\AdsApi\AdManager\AdManagerSessionBuilder;
use Google\AdsApi\AdManager\v202502\ServiceFactory;
use Google\AdsApi\AdManager\v202502\AdUnit;
use Google\AdsApi\AdManager\v202502\AdUnitSize;
use Google\AdsApi\AdManager\v202502\EnvironmentType;
use Google\AdsApi\AdManager\v202502\Size;
use Google\AdsApi\AdManager\v202502\AdUnitTargetWindow;
use App\Models\AdUnit as AdUnitModel;
use App\Models\GoogleAdSetting;

class CreateAdUnitController extends Controller
{
    public function store(Request $request): JsonResponse
    {

        // 1. Validate input
        $request->validate([
            'name'             => 'required|string',
            'position'         => [
                'required',
                'string',
                'regex:/^(header|footer|interstitial|in_page|custom_[A-Za-z0-9_]+)$/'
            ],
            'website_id'       => 'required|integer',
            'code'             => 'required|string',
            'in_page_position' => 'nullable|integer',
        ]);

        // 2. Define only fixed‐pixel sizes + special formats
        $adSizes = [
            'header'       => [['width' => 336, 'height' => 280],['format' => 'Fluid']],
            'footer'       => [
                ['width' => 300, 'height' => 50],
                ['width' => 300, 'height' => 100],
                ['width' => 320, 'height' => 50],
                ['width' => 320, 'height' => 100],
                ['width' => 728, 'height' => 90],
                ['format' => 'Fluid'],
            ],
            'interstitial' => [
                ['width'  => 1024, 'height' => 768],
                ['width'  =>  768, 'height' => 1024],
                ['format' => 'Fluid'],
                ['format' => 'Out-of-page'],
            ],
            'in_page'      => [['width' => 336, 'height' => 280],['format' => 'Fluid']],
        ];

        try {
            
            // 3. Extract inputs
            $adUnitName     = $request->input('name');
            $position       = $request->input('position');
            $websiteId      = $request->input('website_id');
            $adUnitCode     = $request->input('code');
            $inPagePosition = $request->input('in_page_position', 0);
            $googlecredentialpath = config('app.google.ads_path') . "/{$websiteId}/" . config('app.google.ads_file_name');
            $googleSetting = GoogleAdSetting::where('website_id', $websiteId)->first();
            $networkCode = $googleSetting?->network_code ?? null;

            if (!$networkCode) {
                return response()->json([
                    'error' => 'network_code not found for this website.',
                ], 400);
            }


            // 4. Build Ad Manager API session
            $oAuth2Credential = (new OAuth2TokenBuilder())
                ->fromFile($googlecredentialpath)
                ->build();

            $session = (new AdManagerSessionBuilder())
                ->fromFile($googlecredentialpath)
                ->withOAuth2Credential($oAuth2Credential)
                ->build();

            $factory          = new ServiceFactory();
            $inventoryService = $factory->createInventoryService($session);
            $networkService   = $factory->createNetworkService($session);

            // 5. Get root ad unit ID
            $rootAdUnitId = $networkService
                ->getCurrentNetwork()
                ->getEffectiveRootAdUnitId();

            // 6. Pick sizes; detect fluid flag (custom_* → header)
            $key       = str_starts_with($position, 'custom_') ? 'header' : $position;
            $rawSizes  = $adSizes[$key] ?? [];

            $pixelSizes = array_filter($rawSizes, fn($s) => isset($s['width'], $s['height']));
            $specials   = array_filter($rawSizes, fn($s) => isset($s['format']));
            $hasFluid   = in_array('Fluid', array_column($specials, 'format'), true);

            if (empty($pixelSizes)) {
                return response()->json([
                    'error' => 'Invalid position or no pixel sizes configured.',
                ], 400);
            }

            // 7. Build size‐string for DB ([w,h], …, fluid)
            $sizeStringParts = array_map(
                fn($s) => "[{$s['width']},{$s['height']}]",
                $pixelSizes
            );
            if ($hasFluid) {
                $sizeStringParts[] = '"fluid"';
            }
            $sizeString = implode(', ', $sizeStringParts);

            // 8. Create AdUnitSize objects (pixel only)
            $adUnitSizes = [];
            foreach ($pixelSizes as $s) {
                $sz = new Size($s['width'], $s['height'], false);
                $au = (new AdUnitSize())
                    ->setSize($sz)
                    ->setEnvironmentType(EnvironmentType::BROWSER);
                $adUnitSizes[] = $au;
            }

            // 9. Build the AdUnit
            $adUnit = (new AdUnit())
                ->setParentId($rootAdUnitId)
                ->setName($adUnitName)
                ->setAdUnitCode($adUnitCode)
                ->setDescription('Auto‐created via API')
                ->setTargetWindow(AdUnitTargetWindow::BLANK)
                ->setAdUnitSizes($adUnitSizes)
                ->setIsInterstitial($position === 'interstitial')
                ->setIsNative(false)
                ->setIsFluid($hasFluid);

            // 10. Create via API
            $createdUnits = $inventoryService->createAdUnits([$adUnit]);
            if (empty($createdUnits)) {
                return response()->json([
                    'error' => 'Google Ad Manager did not return a created unit.',
                ], 500);
            }
            $created = $createdUnits[0];
            $concatenatedAdUnitCode = '/' . $networkCode . '/' . $created->getAdUnitCode();

            // 11. Persist in local DB
            $record = new AdUnitModel();
            $record->website_id       = $websiteId;
            $record->adunit_name      = $created->getName();
            $record->ad_unit_code     = $concatenatedAdUnitCode;
            $record->adunit_id        = $created->getId();
            $record->ad_unit_size     = $sizeString;
            $record->ad_unit_type     = $position;
            $record->in_page_position = $inPagePosition;
            $record->save();

            // 12. Return success
            return response()->json([
                'message' => 'Ad unit created successfully!',
                'data'    => [
                    'id'     => $created->getId(),
                    'name'   => $created->getName(),
                    'code'   => $created->getAdUnitCode(),
                    'status' => $created->getStatus(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Ad unit creation failed: ' . $e->getMessage(), [
                'position'  => $position,
                'websiteId' => $websiteId,
            ]);
            return response()->json([
                'error' => 'Failed to create ad unit.',
                'debug' => $e->getMessage(),
            ], 500);
        }
    }
}
