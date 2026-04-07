<?php

namespace App\Http\Controllers;

use App\Models\Website;
use App\Models\GoogleAdSetting;
use App\Models\Vertical;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Theme;
use Google\Analytics\Admin\V1beta\Client\AnalyticsAdminServiceClient;
use Google\Analytics\Admin\V1beta\CreatePropertyRequest;
use Google\Analytics\Admin\V1beta\Property;
use Google\Analytics\Admin\V1beta\IndustryCategory;
use Google\Analytics\Admin\V1beta\PropertyType;
use GuzzleHttp\Client;
use Illuminate\Http\RedirectResponse;
use App\Services\WebsiteCacheService;

class WebsiteController extends Controller
{
    protected $cacheService;

    public function __construct(WebsiteCacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }
    public function create()
    {
        $themes = Theme::where('status_flag', 0)->get();
        $verticals = Vertical::get();
        return view('pages/websites.create', compact('themes', 'verticals'));
    }

    public function index()
    {
        // Fetch only active websites (status_flag = 0)
        $websites = Website::paginate(10);
        return view('pages/websites.index', compact('websites'));
    }

    public function store(Request $request)
    {
        $validated   = $this->validateWebsite($request);
        $apiKey      = (string) config('app.AAPANEL_API_KEY');
        $panelUrl    = (string) config('app.AAPANEL_PANEL_URL');
        $websitemode = (string) config('app.env');

        DB::beginTransaction();

        try {
            // 1) call aaPanel only if not in local
            $siteId = null;
            if ($websitemode !== 'local') {
                $aapanelResponse = $this->addWebsiteToAapanel($validated['domain'], $apiKey, $panelUrl);
                if (! ($aapanelResponse['status'] ?? false)) {
                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            'Error creating website in AAPanel: '
                                . ($aapanelResponse['error'] ?? 'unknown')
                                . ' | ' . json_encode($aapanelResponse['creationResult'] ?? [])
                        );
                }
                $siteId = $aapanelResponse['siteId'];
            }

            // 2) create Website (aapanel_site_id will be null in local)
            $website = Website::create(array_merge(
                $validated,
                ['aapanel_site_id' => $siteId]
            ));

            // 3) process logo & favicon
            $updateData = [];
            if ($request->hasFile('logo_path')) {
                $updateData['logo_path'] = $this->processImage(
                    $request->file('logo_path'),
                    $website->id,
                    'logo.webp',
                    3840,
                    1232
                );
            }
            if ($request->hasFile('favicon_path')) {
                $updateData['favicon_path'] = $this->processImage(
                    $request->file('favicon_path'),
                    $website->id,
                    'favicon.webp',
                    128,
                    128
                );
            }
            if (!empty($updateData)) {
                $website->update($updateData);
            }


            DB::commit();

            return redirect()
                ->route('websites.index')
                ->with('success', 'Website created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }


    public function edit(Website $website)
    {
        $themes = Theme::where('status_flag', 0)->get();
        $verticals = Vertical::get();
        return view('pages/websites.create', compact('website', 'themes', 'verticals'));
    }

    public function update(Request $request, Website $website)
    {
        $validated = $this->validateWebsite($request);

        DB::beginTransaction();
        try {
            $directory = "website-logo/{$website->id}";

            // Handle logo update
            if ($request->hasFile('logo_path')) {

                // Delete only if it exists in the correct folder
                if ($website->logo_path && Storage::disk('public')->exists("$directory/logo.webp")) {
                    Storage::disk('public')->delete("$directory/logo.webp");
                }
                $validated['logo_path'] = $this->processImage($request->file('logo_path'), $website->id, 'logo.webp', 3840, 1232);
            }

            // Handle favicon update
            if ($request->hasFile('favicon_path')) {
                // Delete only if it exists in the correct folder
                if ($website->favicon_path && Storage::disk('public')->exists("$directory/favicon.webp")) {
                    Storage::disk('public')->delete("$directory/favicon.webp");
                }
                $validated['favicon_path'] = $this->processImage($request->file('favicon_path'), $website->id, 'favicon.webp', 128, 128);
            }

            $website->update($validated);

            DB::commit();
            return redirect()->route('websites.index')->with('success', 'Website updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating website: ' . $e->getMessage());
        }
    }
    public function destroy(Website $website, Request $request)
    {

        try {
            $websiteId = $website->id;
            $newStatus = $request->input('status_flag');
            DB::statement("CALL update_website_status(?, ?)", [$websiteId, $newStatus]);
            $message = ($newStatus == 0) ? 'Website activated successfully!' : 'Website deactivated successfully.';

            return redirect()->route('websites.index')->with('success', $message);
        } catch (\Exception $e) {

            return back()->with('error', 'Error updating website: ' . $e->getMessage());
        }
    }
    private function validateWebsite(Request $request): array
    {
        return $request->validate([
            'website_name'       => 'required|string|max:255',
            'company_name'       => 'nullable|string|max:255',
            'company_address'    => 'nullable|string|max:500',
            'email'              => 'nullable|email|max:255',
            'contact'            => 'nullable|string|max:20',
            'logo_path'          => 'nullable|image|mimes:jpeg,png,webp|max:200',
            'favicon_path'       => 'nullable|image|mimes:jpeg,png,webp,ico|max:100',
            'website_theme'      => 'required|string|max:255',
            'domain'             => 'required|string|max:255',
            'website_vertical'   => 'required|string|max:255',
            'website_type'       => 'required|string|in:blog,quiz',
        ]);
    }


    private function processImage($image, $websiteId, $fileName, $width = 256, $height = 256)
    {
        if (! $image) {
            return null;
        }

        try {
            $directory = "website-logo/{$websiteId}";
            $imagePath = "{$directory}/{$fileName}";

            Storage::disk('public')->makeDirectory($directory, 0755, true);

            $manager = new ImageManager(['driver' => 'gd']);
            $img = $manager->make($image)
                ->resize($width, $height, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });

            $webpData = $img->encode('webp', 30);

            Storage::disk('public')->put($imagePath, $webpData);

            return $fileName;
        } catch (\Exception $e) {

            return back()->with('error', 'Image processing error: ' . $e->getMessage());
        }
    }
    public function toggleAnalytics(Request $request, Website $website)
    {
        if (! $request->has('ga_property_id')) {
            return back();
        }

        try {
            $googleCredentialPath = config('app.google.credentials_path')
                . "/{$website->id}/" . config('app.google.service_file_name');

            $settings = GoogleAdSetting::where('website_id', $website->id)->first();
            if (! $settings) {
                return back()->with('error', 'Google Ad settings not found for this website.');
            }

            $client = new AnalyticsAdminServiceClient([
                'credentials' => $googleCredentialPath,
            ]);

            $accountId = (string) $settings->ga4_account_id;
            $parent    = "accounts/{$accountId}";

            $property = new Property([
                'display_name'      => $website->domain,
                'time_zone'         => 'America/Los_Angeles',
                'currency_code'     => 'USD',
                'industry_category' => IndustryCategory::TECHNOLOGY,
                'property_type'     => PropertyType::PROPERTY_TYPE_ORDINARY,
                'parent'            => $parent,
            ]);

            $createRequest = new CreatePropertyRequest(['property' => $property]);
            $gaProperty    = $client->createProperty($createRequest);

            $propertyId = explode('/', $gaProperty->getName())[1];
            $website->ga_property_id = $propertyId;
            $website->save();

            return back()->with('success', 'Google Analytics enabled (Property ID: ' . $propertyId . ').');
        } catch (\Throwable $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    public function toggleAds(Request $request, Website $website)
    {
        $website->is_ads_live = $request->has('is_ads_live') ? 1 : 0;
        $website->save();

        return back()->with('success', 'Ad status updated successfully.');
    }


    private function addWebsiteToAapanel($domain, $apiKey, $panelUrl)
    {
        $client = new \GuzzleHttp\Client();

        $getToken = function () use ($apiKey) {
            $time = time();
            return [
                'request_time' => $time,
                'request_token' => md5($time . md5($apiKey))
            ];
        };

        $addSiteEndpoint = (string) config('app.AAPANEL_ADD_SITE_ENDPOINT');
        $setRunPathEndpoint = (string) config('app.AAPANEL_SET_RUN_PATH_ENDPOINT');
        $websiteSavePath = (string) config('app.AAPANEL_WEBSITE_PATH');
        $websiteRunPath = (string) config('app.AAPANEL_WEBSITE_RUN_PATH');
        $saveFileEndpoint = (string) config('app.AAPANEL_SAVE_FILE_ENDPOINT');
        $nginxConfBasePath = (string) config('app.AAPANEL_NGINX_CONF_BASE_PATH');
        $nginxurlConfBasePath = (string) config('app.AAPANEL_NGINX_URL_CONF_BASE_PATH');
        $getFileEndpoint = (string) config('app.AAPANEL_GET_FILE_ENDPOINT');

        $webname = json_encode([
            'domain' => $domain,
            'domainlist' => [],
            'count' => 0,
        ]);

        $token = $getToken();

        $postData = [
            'request_time' => $token['request_time'],
            'request_token' => $token['request_token'],
            'webname' => $webname,
            'path' => $websiteSavePath,
            'type_id' => 0,
            'type' => 'PHP',
            'version' => '83',
            'port' => 80,
            'ps' => 'Added via Admin Panel',
            'ftp' => 'false',
            'sql' => 'false',
            'set_ssl' => '0',
        ];

        $url = rtrim($panelUrl, '/') . $addSiteEndpoint;

        try {
            $response = $client->post($url, [
                'form_params' => $postData,
                'timeout' => 30,
                'verify' => false,
            ]);

            $result = json_decode($response->getBody(), true);


            if (isset($result['siteStatus']) && $result['siteStatus']) {
                if (isset($result['siteId'])) {
                    $siteId = $result['siteId'];

                    $token = $getToken();

                    $runPathData = [
                        'request_time' => $token['request_time'],
                        'request_token' => $token['request_token'],
                        'id' => $siteId,
                        'runPath' => $websiteRunPath,
                    ];

                    $runPathUrl = rtrim($panelUrl, '/') . $setRunPathEndpoint;

                    $runPathResponse = $client->post($runPathUrl, [
                        'form_params' => $runPathData,
                        'timeout' => 30,
                        'verify' => false,
                    ]);

                    $runPathResult = json_decode($runPathResponse->getBody(), true);

                    if (isset($runPathResult['status']) && $runPathResult['status']) {
                        // Fetch existing rewrite rules
                        $token = $getToken();

                        $rewriteUrl = rtrim($panelUrl, '/') . $getFileEndpoint;
                        $rewriteFetchData = [
                            'request_time' => $token['request_time'],
                            'request_token' => $token['request_token'],
                            'path' => $nginxurlConfBasePath,
                        ];

                        $rewriteResponse = $client->post($rewriteUrl, [
                            'form_params' => $rewriteFetchData,
                            'timeout' => 30,
                            'verify' => false,
                        ]);

                        $rewriteResult = json_decode($rewriteResponse->getBody(), true);
                        $finalRewriteContent = $rewriteResult['data'] ?? '';

                        // Save config file
                        $token = $getToken();
                        $filePath = rtrim($nginxConfBasePath, '/') . '/' . $domain . '.conf';

                        $fileData = [
                            'request_time' => $token['request_time'],
                            'request_token' => $token['request_token'],
                            'path' => $filePath,
                            'data' => $finalRewriteContent,
                            'encoding' => 'utf-8',
                        ];

                        $fileResponse = $client->post(rtrim($panelUrl, '/') . $saveFileEndpoint, [
                            'form_params' => $fileData,
                            'timeout' => 30,
                            'verify' => false,
                        ]);

                        $fileResult = json_decode($fileResponse->getBody(), true);

                        if (isset($fileResult['status']) && $fileResult['status']) {
                            return [
                                'status' => true,
                                'siteId' => $siteId,
                                'error' => '',
                                'creationResult' =>  'Website created, run path set, and rewrite config saved successfully.'
                            ];
                        } else {
                            return [
                                'status' => false,
                                'error' => 'Run path set, but failed to save rewrite config file',
                                'creationResult' => $fileResult,
                            ];
                        }
                    } else {
                        return [
                            'status' => false,
                            'error' => 'Website created but failed to set run path',
                            'creationResult' => $runPathResult,
                        ];
                    }
                }
            } else {
                return [
                    'status' => false,
                    'error' => 'Failed to create website',
                    'creationResult' => $result,
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function purgeCache(Website $website): RedirectResponse
    {
        if (!$website->cloudflare_zone_id) {
            return back()->with('error', "❌ Cloudflare zone ID not configured for {$website->domain}");
        }

        $cfg = config('app.cloudflare');
        $client = new Client(['base_uri' => $cfg['base_uri']]);

        try {
            $response = $client->post("zones/{$website->cloudflare_zone_id}/purge_cache", [
                'headers' => [
                    'X-Auth-Email' => $cfg['auth_email'],
                    'X-Auth-Key'   => $cfg['auth_key'],
                    'Content-Type' => 'application/json',
                ],
                'json' => ['purge_everything' => true],
            ]);

            $body = json_decode((string) $response->getBody(), true);

            if (!empty($body['success'])) {
                return back()->with('success', "🔄 Cache purged for {$website->domain}");
            }

            if (!empty($body['errors'])) {
                $errors = array_map(fn($err) => "[{$err['code']}] {$err['message']}", $body['errors']);
                return back()->with('error', '❌ Purge failed: ' . implode('; ', $errors));
            }

            return back()->with('error', '❌ Purge failed: Unknown error.');
        } catch (\Exception $e) {
            return back()->with('error', '❌ Purge request failed: ' . $e->getMessage());
        }
    }

    public function pauseZone(Request $request, Website $website): RedirectResponse
    {
        if (!$website->cloudflare_zone_id) {
            return back()->with('error', "❌ Cloudflare zone ID not configured for {$website->domain}");
        }

        $pause = $request->has('paused');
        $cfg   = config('app.cloudflare');
        $client = new Client([
            'base_uri'    => $cfg['base_uri'],
            'http_errors' => false,
        ]);

        try {
            $response = $client->patch("zones/{$website->cloudflare_zone_id}", [
                'headers' => [
                    'X-Auth-Email' => $cfg['auth_email'],
                    'X-Auth-Key'   => $cfg['auth_key'],
                    'Content-Type' => 'application/json',
                ],
                'json' => ['paused' => $pause],
            ]);

            $body = json_decode((string) $response->getBody(), true);

            if (!empty($body['errors'])) {
                foreach ($body['errors'] as $err) {
                    if ($err['code'] === 1019) {
                        $website->cloudflare_paused = $pause;
                        $website->save();
                        return back()->with('success', "⏸️ Cloudflare paused for {$website->domain}");
                    }
                }
                $errs = array_map(fn($e) => "[{$e['code']}] {$e['message']}", $body['errors']);
                return back()->with('error', '❌ Pause failed: ' . implode('; ', $errs));
            }

            if (!empty($body['success'])) {
                $website->cloudflare_paused = $pause;
                $website->save();
                $msg = $pause
                    ? "⏸️ Cloudflare paused for {$website->domain}"
                    : "▶️ Cloudflare resumed for {$website->domain}";
                return back()->with('success', $msg);
            }

            return back()->with('error', '❌ Pause failed: Unknown error.');
        } catch (\Exception $e) {
            return back()->with('error', '❌ Pause request failed: ' . $e->getMessage());
        }
    }
    public function clearCache(Request $request, Website $website)
    {
        $error = $this->cacheService->refreshCacheForHost($website->domain);
    
        if ($error) {
            return back()->with('error', 'Cache not found for this website.');
        }
    
        return back()->with('success', "Cache refreshed for {$website->domain}.");
    }
    public function clearAllCache(Request $request)
    {
        $summary = $this->cacheService->refreshCacheForAllWebsites();
        $message = $summary['ok']
            ? "Cache refreshed for all {$summary['total']} websites."
            : "Cache refreshed for {$summary['ok_count']} of {$summary['total']} websites. Failed: {$summary['error_count']}.";
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(
                [
                    'success' => (bool) $summary['ok'],
                    'ok'      => (bool) $summary['ok'],
                    'message' => $message,
                ] + $summary,
                $summary['ok'] ? 200 : 422
            );
        }
        if (!$summary['ok']) {
            return back()->with('error', $message);
        }
        return back()->with('success', $message);
    }
    public function viewCache(Website $website)
    {
       
        $cacheData = $this->cacheService->getWebsiteContext($website->domain);

        if (! $cacheData) {
            return back()->with('error', 'Cache not found for this website.');
        }

        return view('pages.websites.view-cache', compact('website', 'cacheData'));
    }
}
