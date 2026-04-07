<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BulkPageRenderer;

class RenderPages extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan pages:render website_id
     * @var string
     */
    protected $signature = 'pages:render {website_id : The ID of the website whose pages should be re-rendered}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-render all active pages for a given website (status_flag = 0 and is_ads_live = true)';

    /**
     * Execute the console command.
     */
   public function handle(): int
   {
    $websiteId = (int) $this->argument('website_id');

    $this->info("Starting bulk render for website_id = {$websiteId}...");

    /** @var BulkPageRenderer $renderer */
    $renderer = app(BulkPageRenderer::class);
    $result = $renderer->renderForWebsite($websiteId);

    if ($result['success']) {
        $this->info("✅ Done re-rendering pages for website {$websiteId}.");
        $this->info("👉 Processed: {$result['processed']}, Failed: {$result['failed']}");
    } else {
        $this->error("❌ Rendering failed for website {$websiteId}. Check logs.");
        $this->error("👉 Processed: {$result['processed']}, Failed: {$result['failed']}");
        return 1;
    }

    return 0;
}

}
