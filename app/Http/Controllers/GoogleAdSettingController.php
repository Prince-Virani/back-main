<?php

namespace App\Http\Controllers;

use App\Models\GoogleAdSetting;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

final class GoogleAdSettingController extends Controller
{
    public function index()
    {
        $settings = GoogleAdSetting::with('website')->paginate(15);
        return view('pages.google_ads_setting.index', compact('settings'));
    }

    public function create()
    {
        $usedIds  = GoogleAdSetting::pluck('website_id')->all();
        $websites = Website::whereNotIn('id', $usedIds)->pluck('website_name', 'id');

        return view('pages.google_ads_setting.create', compact('websites'));
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules($request));

        DB::beginTransaction();
        try {
            $data['credentials_path'] = $this->saveCredentials(
                $request->file('credentials_file'),
                (int) $data['website_id']
            );

            $data['status'] = $request->has('status');
            $setting = GoogleAdSetting::create($data);

            $this->generateAdsApiIni($setting);

            DB::commit();

            return redirect()
                ->route('google-settings.index')
                ->with('success', 'Google Ads setting saved.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to save Google Ads setting: ' . $e->getMessage());
        }
    }

    public function edit(GoogleAdSetting $google_setting)
    {
        $websites = Website::pluck('website_name', 'id');

        return view('pages.google_ads_setting.create', [
            'google_setting' => $google_setting,
            'websites'       => $websites,
        ]);
    }

    public function update(Request $request, GoogleAdSetting $google_setting)
    {
        $data = $request->validate($this->rules($request, $google_setting));

        DB::beginTransaction();
        try {

            if ($request->hasFile('credentials_file')) {
                $data['credentials_path'] = $this->saveCredentials(
                    $request->file('credentials_file'),
                    (int) $data['website_id']
                );
            } else {
                $data['credentials_path'] = $google_setting->credentials_path;
            }

            $data['status'] = $request->has('status');

            $google_setting->update($data);

            $this->generateAdsApiIni($google_setting->fresh());

            DB::commit();

            return redirect()
                ->route('google-settings.index')
                ->with('success', 'Google Ads setting updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update Google Ads setting: ' . $e->getMessage());
        }
    }

    protected function rules(Request $request, ?GoogleAdSetting $existing = null): array
    {
        $hasExistingCreds = !empty($existing?->credentials_path);
        $fileRule = ($request->isMethod('post') || !$hasExistingCreds) ? 'required' : 'nullable';

        return [
            'website_id' => [
                'required',
                'exists:websites,id',
                Rule::unique('google_ad_settings', 'website_id')->ignore($existing?->id),
            ],
            'network_code'               => 'nullable|string|max:150',
            'google_adsense_name'        => 'required|string|max:255',
            'adx_name'                   => 'required|string|max:255',
            'status'                     => 'sometimes|boolean',

            'credentials_file'           => [$fileRule, 'file', 'mimes:json'],

            'ga_ad_unit_run_id'          => 'nullable|string|max:50',
            'ga_order_id'                => 'nullable|string|max:50',
            'ga_advertiser_id'           => 'nullable|string|max:50',
            'ga_custom_targeting_key_id' => 'nullable|string|max:50',
            'ga_web_property'            => 'nullable|string|max:100',
            'ga4_account_id'             => 'nullable|string|max:50',
        ];
    }


    protected function saveCredentials($file, int $siteId): string
    {
        if (!$file) {
            throw new \InvalidArgumentException('No credentials file provided.');
        }

        $filename = $file->getClientOriginalName();
        $dir      = storage_path("credentials/{$siteId}");

        if (File::isDirectory($dir)) {
            File::cleanDirectory($dir);
        } else {
            File::makeDirectory($dir, 0755, true);
        }

        $file->move($dir, $filename);

        return $filename;
    }


    protected function generateAdsApiIni(GoogleAdSetting $setting): void
    {
        $ds = DIRECTORY_SEPARATOR;
        $siteId = $setting->website_id;

        $templatePath = base_path("secure{$ds}google-ads{$ds}adsapi_php.ini");
        $targetDir    = base_path("secure{$ds}google-ads{$ds}{$siteId}");
        $targetFile   = "{$targetDir}{$ds}adsapi_php.ini";

        $credRel = (string) $setting->credentials_path;
        if (Str::startsWith($credRel, ['/', '\\'])) {
            $candidate = $credRel;
        } else {
            $perSite = storage_path("credentials{$ds}{$siteId}{$ds}{$credRel}");
            $candidate = File::exists($perSite)
                ? $perSite
                : storage_path("credentials{$ds}{$credRel}");
        }

        $realJson = realpath($candidate);
        if ($realJson === false) {
            throw new \RuntimeException("Credentials not found: {$candidate}");
        }
        $jsonPath = str_replace(['/', '\\'], $ds, $realJson);

        $content = File::get($templatePath);
        $content = preg_replace('/^(\s*networkCode\s*=\s*).*/mi', '$1"' . $setting->network_code . '"', $content);
        $content = preg_replace('/^(\s*jsonKeyFilePath\s*=\s*).*/mi', '$1"' . $jsonPath . '"', $content);

        if (!File::isDirectory($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        File::put($targetFile, $content);
    }
}
