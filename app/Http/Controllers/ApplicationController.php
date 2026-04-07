<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\AppAd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Nelexa\GPlay\GPlayApps;
use Nelexa\GPlay\Model\AppId;
use Illuminate\Support\Facades\Response;
use App\Services\EncryptionService;


class ApplicationController extends Controller
{
    public function index()
    {
        $applications = Application::paginate(5);
        return view('pages.applications.index', compact('applications'));
    }

    public function create()
    {
        $ads = null;
        return view('pages.applications.create', compact('ads'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'application_name' => 'required|string|max:255',
            'package_name' => 'required|string|max:255|unique:applications',
        ]);

        try {
            $application = Application::create($validated);
            return redirect()->route('applications.index')->with('success', 'Application created successfully!');
        } catch (\Exception $e) {
            Log::error('Application Store Error: ' . $e->getMessage());
            return redirect()->back()->withErrors('Something went wrong. Please try again.');
        }
    }

    public function edit(Application $application)
    {
        $applicationAds = \DB::table('application_ads')->where('application_id', $application->id)->first();
        $ads = [];

        if ($applicationAds && $applicationAds->ads) {
            $decoded = json_decode($applicationAds->ads, true);
            if (is_array($decoded)) {
                $ads = $decoded;
            }
        }

        return view('pages.applications.create', compact('application', 'ads'));
    }


    public function update(Request $request, $applicationId)
    {
        try {
            $validated = $request->validate([
                'application_name' => 'required|string|max:255',
                'package_name'     => 'required|string|max:255',
                'ads'              => 'nullable|array',
                'firestore_json'   => 'nullable|string',
            ]);

            $application = Application::findOrFail($applicationId);

            $application->update([
                'application_name' => $validated['application_name'],
                'package_name'     => $validated['package_name'],
            ]);

            $firestoreData = [];
            if ($request->filled('firestore_json')) {
                $firestoreData = json_decode($request->input('firestore_json'), true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return back()->with('error', 'Invalid Firestore JSON data.');
                }
            } elseif ($request->filled('ads')) {
                $firestoreData = $validated['ads'];
            }

            if (!empty($firestoreData)) {
                AppAd::updateOrCreate(
                    [
                        'application_id' => $application->id,
                        'package_name'   => $validated['package_name']
                    ],
                    [
                        'ads' => $firestoreData
                    ]
                );
            }

            return redirect()->route('applications.index')->with('success', 'Application updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }


    public function fetchData($applicationId)
    {
        $app = Application::findOrFail($applicationId);
        $gplay = new GPlayApps();
        $pkg = $app->package_name;
        $maybe = fn(object $o, string $method) => method_exists($o, $method) ? $o->{$method}() : null;

        try {
            $info = $gplay->getAppInfo(new AppId($pkg));

            $app->update([
                'title'              => $info->getName(),
                'rating'             => $info->getScore(),
                'installs'           => $info->getInstalls(),
                'play_store'         => $info->getUrl(),
                'icon_url'           => $info->getIcon()?->getUrl(),
                'contains_ads'       => $info->isContainsAds(),
                'app_size'           => $info->getSize(),
                'app_version'        => $info->getAppVersion(),
                'first_released'     => $info->getReleased()?->format('Y-m-d'),
                'last_updated'       => $info->getUpdated()?->format('Y-m-d'),
                'privacy_policy_url' => $maybe($info, 'getPrivacyPoliceUrl') ?? $maybe($info, 'getPrivacyPolicyUrl'),
            ]);

            $app->play_meta = [
                'id'             => $info->getId(),
                'locale'         => $info->getLocale(),
                'country'        => $info->getCountry(),
                'full_url'       => $info->getFullUrl(),
                'summary'        => $info->getSummary(),
                'description'    => $info->getDescription(),
                'recent_changes' => $info->getRecentChanges(),
                'media'          => [
                    'cover'       => $info->getCover()?->getUrl(),
                    'video'       => $info->getVideo()?->getUrl(),
                    'screenshots' => array_map(fn($i) => $i->getUrl(), $info->getScreenshots() ?? []),
                ],
                'popularity'     => [
                    'installs_text' => $info->getInstallsText(),
                    'voters'        => $info->getNumberVoters(),
                    'reviews_count' => $info->getNumberReviews(),
                    'histogram'     => $info->getHistogramRating()?->asArray(),
                ],
                'pricing'        => [
                    'is_free'    => $info->isFree(),
                    'price'      => $info->getPrice(),
                    'price_text' => $info->getPriceText(),
                    'currency'   => $info->getCurrency(),
                    'has_iap'    => $info->isContainsIAP(),
                ],
                'developer'      => [
                    'id'      => $info->getDeveloper()?->getId(),
                    'name'    => $info->getDeveloperName(),
                    'url'     => $info->getDeveloper()?->getUrl(),
                    'website' => $info->getDeveloper()?->getWebsite(),
                    'email'   => $info->getDeveloper()?->getEmail(),
                    'address' => $info->getDeveloper()?->getAddress(),
                ],
                'category'       => [
                    'id'      => $info->getCategory()?->getId(),
                    'name'    => $info->getCategory()?->getName(),
                    'is_game' => $info->getCategory()?->isGamesCategory(),
                ],
                'content_rating' => $info->getContentRating(),
                'android'        => [
                    'version' => $info->getAndroidVersion(),
                    'min'     => $info->getMinAndroidVersion(),
                ],
                'flags'          => [
                    'editors_choice' => $info->isEditorsChoice(),
                ],
            ];

            $app->save();

            return redirect()->route('applications.index')
                ->with('success', 'App data fetched successfully.');
        } catch (\Throwable $e) {
            return redirect()->route('applications.index')
                ->with('error', 'Failed to fetch app info: ' . $e->getMessage());
        }
    }
    public function getAppAdsByPackage(Request $request)
    {
        try {
            $signatureHash = $request->attributes->get('validated_signature_hash');
            $packageName = $request->attributes->get('validated_package_name');

            $adsRow = AppAd::where('package_name', $packageName)
                ->where('status_flag', 0)
                ->value('ads');

            if (empty($adsRow)) {
                return response()->json([
                    'status' => 'error',
                    'code' => 204,
                    'message' => 'No active ads found for this package.',
                    'data' => null
                ], 200);
            }
            $jsonData = is_string($adsRow) ? $adsRow : json_encode($adsRow);
            Log::info('JSON Data to Encrypt', ['json' => $jsonData]);
            $key = hash('sha256', $signatureHash, true);
            $encrypted = EncryptionService::encrypt($jsonData, $key);

            return response()->json([
                'status' => 'success',
                'code' => 200,
                'message' => 'Ads fetched and encrypted successfully.',
                'data' => $encrypted
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'code' => 500,
                'message' => 'An error occurred during the encryption process.',
                'data' => null,
                'error' => $e->getMessage()
            ], 200);
        }
    }
    public function decryptAppAds($encryptedData)
    {
        try {
            $secret = config('app.encryption.encryption_key');
            $key = hash('sha256', $secret, true);

            // Decrypt without passing IV explicitly
            $decrypted = EncryptionService::decrypt($encryptedData, $key);

            if (!$decrypted) {
                return response()->json(['message' => 'Decryption failed.'], 500);
            }

            return response()->json([
                'data' => json_decode($decrypted)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred during the decryption process.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function deleteApiKeyFromBody(Request $request, $applicationId)
    {
        $request->validate([
            'key' => 'required|string',
        ]);

        $key = $request->input('key');
        $application = Application::findOrFail($applicationId);

        $apiKeys = array_filter(
            explode(',', $application->api_keys),
            fn($k) => trim($k) !== $key
        );

        $application->api_keys = implode(',', $apiKeys);
        $application->save();

        return response()->json(['success' => true]);
    }
    public function viewPlayJson(Application $application)
    {
        $data = $application->play_json ?? $application->play_meta ?? null;

        if (!$data) {
            return response()->json(['error' => 'No JSON stored for this app.'], 404);
        }
        if (is_string($data)) {
            $decoded = json_decode($data, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data = $decoded;
            }
        }
        return view('pages.applications.view-play-json', [
            'application' => $application,
            'data'        => $data,
        ]);
    }
}
