<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use App\Models\Website;
use App\Models\Commonpage;
use App\Models\Page;
use App\Models\AdUnit;
use App\Models\TagManager;

class WebsiteCacheService
{
    protected string $disk = 'shared';

    public function refreshCacheForHost(string $host): ?string
    {
        $errorMsg = 'Cache not found for this website.';

        try {
            $host = trim($host);

            $websitedetails = Website::where('domain', $host)->first();
            if (! $websitedetails) {
                if (method_exists($this, 'deleteCacheFile')) {
                    $this->deleteCacheFile($host);
                }
                return $errorMsg;
            }

            $websiteId = $websitedetails->id;

            $commonpages = Commonpage::where('website_id', $websiteId)
                ->where('status_flag', 0)
                ->get(['id', 'page_name', 'slug']);

            $allCategories = Page::where('website_id', $websiteId)
                ->where('status_flag', 0)
                ->pluck('categories')
                ->toArray();

            $counts = [];
            foreach ($allCategories as $cats) {
                foreach (explode(',', (string) $cats) as $cat) {
                    $cat = trim($cat);
                    if ($cat !== '') {
                        $counts[$cat] = ($counts[$cat] ?? 0) + 1;
                    }
                }
            }

            $ads = collect();
            if ($websitedetails->is_ads_live) {
                $ads = AdUnit::where('website_id', $websiteId)
                    ->where('status_flag', true)
                    ->get()
                    ->groupBy('ad_unit_type');
            }
            $PageActiveCount = $ads->get('in_page', collect())->count();

            $tagManagers = TagManager::where('website_id', $websiteId)
                ->where('status_flag', 0)
                ->get();

            $cacheData = [
                'websitedetails'  => $websitedetails->toArray(),
                'commonpages'     => $commonpages->toArray(),
                'categoryCounts'  => $counts,
                'ads'             => $ads->map(fn($group) => $group->toArray())->toArray(),
                'PageActiveCount' => $PageActiveCount,
                'TagManagers'     => $tagManagers->toArray(),
                'generated_at'    => now()->toIso8601String(),
            ];

            $json = json_encode($cacheData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            if ($json === false) {
                return $errorMsg;
            }

            $disk = Storage::disk($this->disk);
            $rootPath = $disk->path('');

            if (! is_dir($rootPath)) {
                if (! @mkdir($rootPath, 0755, true) && ! is_dir($rootPath)) {
                    return $errorMsg;
                }
            }

            $filename = "website_context_{$host}.json";
            $result   = $disk->put($filename, $json);

            if (! $result) {
                return $errorMsg;
            }

            return null;
        } catch (\Throwable $e) {
            return $errorMsg;
        }
    }

    public function refreshCacheForAllWebsites(): array
    {
        $errorMsg = 'Cache not found for this website.';

        $summary = [
            'ok'          => true,
            'total'       => 0,
            'ok_count'    => 0,
            'error_count' => 0,
            'errors'      => [],
        ];

        try {
            Website::query()->select('domain')->chunk(100, function ($websites) use (&$summary, $errorMsg) {
                foreach ($websites as $website) {
                    $summary['total']++;
                    $err = $this->refreshCacheForHost($website->domain);
                    if ($err) {
                        $summary['error_count']++;
                        $summary['errors'][$website->domain] = $errorMsg;
                    } else {
                        $summary['ok_count']++;
                    }
                }
            });

            $summary['ok'] = ($summary['error_count'] === 0);
            return $summary;
        } catch (\Throwable $e) {
            return [
                'ok'          => false,
                'total'       => $summary['total'],
                'ok_count'    => $summary['ok_count'],
                'error_count' => max(1, $summary['error_count']),
                'errors'      => ['__global' => $errorMsg],
            ];
        }
    }

    public function getWebsiteContext(string $host): ?array
    {
        try {
            $filename = "website_context_{$host}.json";

            $disk = Storage::disk($this->disk);
            if (! $disk->exists($filename)) {
                return null;
            }

            $json = $disk->get($filename);

            return json_decode($json, true);
        } catch (\Throwable $e) {
            return null;
        }
    }


    protected function deleteCacheFile(string $host): bool
    {
        try {
            $filename = "website_context_{$host}.json";
            $disk = Storage::disk($this->disk);
            if ($disk->exists($filename)) {
                return $disk->delete($filename);
            }
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
