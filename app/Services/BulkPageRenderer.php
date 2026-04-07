<?php

namespace App\Services;

use App\Models\Page;
use App\Models\Website;
use App\Services\PageRenderer;
use Illuminate\Support\Facades\Log;

class BulkPageRenderer
{
    /**
     * Re-render every active Page for the given website.
     *
     * @param  int  $websiteId
     * @param  int  $chunkSize
     * @return array
     */
    public function renderForWebsite(int $websiteId, int $chunkSize = 100): array
    {
        $site = Website::find($websiteId);
        if (! $site) {
            Log::error("Website ID {$websiteId} not found.");
            return ['success' => false, 'processed' => 0, 'failed' => 0];
        }

        $processed = 0;
        $failed = 0;

        try {
            Page::where('website_id', $websiteId)
                ->where('status_flag', 0)
                ->orderBy('id')
                ->chunk($chunkSize, function ($pages) use ($site, &$processed, &$failed) {
                    foreach ($pages as $page) {
                        try {
                            $page->rendered_content = $site->is_ads_live
                                ? PageRenderer::render($page->content, $page->website_id)
                                : $page->content;

                            $page->save();
                            $processed++;
                        } catch (\Throwable $e) {
                            $failed++;
                            Log::error("Page ID {$page->id} failed: " . $e->getMessage());
                        }
                    }
                });
        } catch (\Throwable $e) {
            Log::error("Error rendering pages for website {$websiteId}: " . $e->getMessage());
            return ['success' => false, 'processed' => $processed, 'failed' => $failed];
        }

        return ['success' => true, 'processed' => $processed, 'failed' => $failed];
    }
}
