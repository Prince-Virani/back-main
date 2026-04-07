<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Category;
use App\Models\Website;
use App\Models\GoogleAdSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Log;
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
use App\Services\PageRenderer;


class PageController extends Controller
{

    public function create()
    {
        try {
            return view('pages/pages.create', [
                'categories' => Category::all(),
                'websites'   => Website::where('status_flag', 0)->get()
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Error loading page creation form: ' . $e->getMessage());
        }
    }



    public function index(Request $request)
    {
        try {

            $query = Page::select('id', 'name', 'status_flag', 'categories', 'website_id', 'updated_at')
                ->with(['website:id,website_name']);


            if ($request->has('website_id') && $request->website_id != 0) {
                $query->where('website_id', $request->website_id);
            }

            if ($request->has('status_flag') && $request->status_flag !== 'null' && $request->status_flag != 2) {
                $query->where('status_flag', $request->status_flag);
            }

            if ($request->has('search') && !empty($request->search)) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            return response()->json($query->latest('updated_at')->paginate(50));
        } catch (\Exception $e) {
            Log::error('Error fetching Common pages: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return back()->with('error', 'Error fetching pages: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $data = $this->validatePage($request);

            if ($request->hasFile('image')) {
                $data['image'] = $this->processImage(
                    $request->file('image'),
                    $request->website_id
                );
            }

            $website = Website::findOrFail($data['website_id']);


            if ($website->is_ads_live) {
                $data['rendered_content'] = PageRenderer::render(
                    $data['content'],
                    $data['website_id']
                );
            } else {
                $data['rendered_content'] = $data['content'];
            }

            $page = Page::create($data);


            // $website = Website::find($request->website_id);
            // $settings  = GoogleAdSetting::where('website_id', $website->id)->first();
            // if (! $settings) {
            //     DB::rollBack();
            //     return back()->with('error', 'Google Ad settings not found for this website.');
            // }


            // $domain = $website ? $website->domain ?? 'everglowinfotech.com' : 'everglowinfotech.com';
            // $lineItemName = "{$domain}_{$page->id}";
            // $targetingValue = "{$domain}_{$page->id}";
            // $googlecredentialpath = config('app.google.ads_path') . "/{$website->id}/" . config('app.google.ads_file_name');


            // $gaResponse = $this->createOrUpdateLineItemForPage(
            //     $lineItemName,
            //     $settings->ga_ad_unit_run_id,
            //     $settings->ga_order_id,
            //     $settings->ga_advertiser_id,
            //     $settings->ga_custom_targeting_key_id,
            //     [$targetingValue],
            //     $settings->ga_web_property,
            //     $googlecredentialpath
            // );

            // if (!$gaResponse['success']) {
            //     throw new \Exception('Failed to create Google Line Item.');
            // }

            // $page->update([
            //     'line_item_id'         => $gaResponse['lineItemId'],
            //     'targeting_key_value'  => $targetingValue,
            // ]);

            DB::commit();

            return redirect()->route('pages.index')->with('success', 'Page created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Error creating page: ' . $e->getMessage());
        }
    }


    public function edit(Page $page)
    {
        try {
            return view('pages/pages.create', [
                'page'       => $page,
                'categories' => Category::all(),
                'websites'   => Website::where('status_flag', 0)->get()
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Error loading edit form: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Page $page)
    {
        try {
            $validated = $this->validatePage($request);

            if ($request->hasFile('image')) {
                $websiteId = $request->website_id; // Get website ID from request

                // Ensure the correct path is targeted for deletion
                $oldImagePath = "websites/{$websiteId}/images/" . basename($page->image);

                if ($page->image && Storage::disk('public')->exists($oldImagePath)) {
                    Storage::disk('public')->delete($oldImagePath);
                }

                $validated['image'] = $this->processImage($request->file('image'), $websiteId);
            }
            $website = Website::findOrFail($request->website_id);

            if ($website->is_ads_live) {
                $validated['rendered_content'] = PageRenderer::render(
                    $validated['content'],
                    $validated['website_id']
                );
            } else {
                $validated['rendered_content'] = $validated['content'];
            }
            // $website = Website::find($request->website_id);
            // $settings  = GoogleAdSetting::where('website_id', $website->id)->first();
            // if (! $settings) {
            //     DB::rollBack();
            //     return back()->with('error', 'Google Ad settings not found for this website.');
            // }
            // $domain = $website ? $website->domain ?? 'everglowinfotech.com' : 'everglowinfotech.com';
            // $lineItemName = "{$domain}_{$page->id}";
            // $targetingValue = "{$domain}_{$page->id}";
            //$googlecredentialpath = config('app.google.ads_path') . "/{$website->id}/" . config('app.google.ads_file_name');
            // $gaResponse = $this->createOrUpdateLineItemForPage(
            //     $lineItemName,
            //     $settings->ga_ad_unit_run_id,
            //     $settings->ga_order_id,
            //     $settings->ga_advertiser_id,
            //     $settings->ga_custom_targeting_key_id,
            //     [$targetingValue],
            //     $settings->ga_web_property,
            //     $googlecredentialpath
            // );
            // if ($gaResponse['success']) {
            //     $page->line_item_id = $gaResponse['lineItemId'];
            //     $page->targeting_key_value = $targetingValue;
            //     $page->update($validated);
            //     return redirect()->route('pages.index')->with('success', 'Page updated successfully!');
            // }
            $page->update($validated);
            return redirect()->route('pages.index')->with('success', 'Page updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Error updating page: ' . $e->getMessage());
        }
    }

    public function destroy(Page $page, Request $request)
    {
        try {
            DB::statement("CALL update_page_status(?, ?, ?)", [
                $page->website_id,
                $page->id,
                $request->input('status_flag')
            ]);

            return redirect()->route('pages.index')->with('success', 'Page status updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting page: ' . $e->getMessage());
        }
    }

    private function validatePage(Request $request): array
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'content'     => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpeg,webp,png,jpg,gif|max:2048',
            'categories'  => 'required|array|min:1',
            'paramlink'   => 'required|string|max:255',
            'website_id'  => 'required|exists:websites,id',
        ], [
            'categories.required' => 'At least one category must be selected.',
        ]);

        // Convert categories array to comma-separated string
        $validated['categories'] = implode(',', $validated['categories']);

        return $validated;
    }


    private function processImage($image, int $websiteId): string
    {
        try {
            $imageName = uniqid('', true) . '.webp';

            $manager = new ImageManager(['driver' => 'gd']);

            $img = $manager
                ->make($image)
                ->resize(1024, 512, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })
                ->encode('webp', 30);

            $folder      = "websites/{$websiteId}/images";
            $storagePath = "{$folder}/{$imageName}";

            Storage::disk('public')->makeDirectory($folder);
            Storage::disk('public')->put($storagePath, (string) $img);

            return "images/{$imageName}";
        } catch (\Exception $e) {
            throw new \Exception(
                'Image processing error: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }

    protected function createOrUpdateLineItemForPage(string $lineItemName, string $adUnitId, int $orderId, int $advertiserId, int $customTargetingKeyId, array $valueNames, string $webProperty, string $googlecredentialpath): array
    {
        try {
            $oauth2 = (new OAuth2TokenBuilder())->fromFile($googlecredentialpath)->build();
            $session = (new AdManagerSessionBuilder())
                ->fromFile($googlecredentialpath)
                ->withOAuth2Credential($oauth2)
                ->build();

            $factory = new ServiceFactory();
            $lineItemService = $factory->createLineItemService($session);
            $creativeService = $factory->createCreativeService($session);
            $licaService = $factory->createLineItemCreativeAssociationService($session);
            $customTargetingService = $factory->createCustomTargetingService($session);

            // 1) Fetch existing targeting values by name
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

            // 2) Create missing values
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
                    $createdValueIds[] = $val->getId();
                }
            }

            $allValueIds = array_merge($existingValueIds, $createdValueIds);
            if (empty($allValueIds)) {
                return ['success' => false, 'message' => 'No custom targeting values available.'];
            }

            // 3) Create Line Item
            $targeting = (new Targeting())
                ->setInventoryTargeting(
                    (new InventoryTargeting())->setTargetedAdUnits([
                        (new AdUnitTargeting())->setAdUnitId($adUnitId)->setIncludeDescendants(true)
                    ])
                )
                ->setRequestPlatformTargeting(
                    (new RequestPlatformTargeting())
                        ->setTargetedRequestPlatforms([RequestPlatform::BROWSER])
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
                    (new CreativePlaceholder())
                        ->setSize(new Size(1, 1, false))
                        ->setCreativeSizeType('IGNORED')
                ])
                ->setCreativeRotationType(CreativeRotationType::EVEN)
                ->setDeliveryRateType('EVENLY')
                ->setStartDateTimeType(StartDateTimeType::IMMEDIATELY)
                ->setUnlimitedEndDateTime(true)
                ->setCostType(CostType::CPM)
                ->setCostPerUnit(new Money('INR', 0))
                ->setPrimaryGoal(
                    (new Goal())
                        ->setUnits(-1)
                        ->setUnitType(UnitType::IMPRESSIONS)
                        ->setGoalType(GoalType::NONE)
                )
                ->setWebPropertyCode($webProperty);

            [$createdLineItem] = $lineItemService->createLineItems([$lineItem]);
            $lineItemId = $createdLineItem->getId();

            // 4) Create Stock Creative
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

            // 5) Attach Creative to Line Item
            $lica = new LineItemCreativeAssociation();
            $lica->setLineItemId($lineItemId);
            $lica->setCreativeId($creativeId);
            $licaService->createLineItemCreativeAssociations([$lica]);

            // 6) Fetch line item again to update targeting with custom criteria
            $lineStmt = (new StatementBuilder())
                ->where('id = :id')
                ->withBindVariableValue('id', $lineItemId)
                ->limit(1);
            $lineItemPage = $lineItemService->getLineItemsByStatement($lineStmt->toStatement());
            if (empty($lineItemPage->getResults())) {
                return ['success' => false, 'message' => "Line item ID {$lineItemId} not found for update."];
            }
            $lineItem = $lineItemPage->getResults()[0];

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

            $lineItemService->updateLineItems([$lineItem]);

            return ['success' => true, 'lineItemId' => $lineItemId];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
