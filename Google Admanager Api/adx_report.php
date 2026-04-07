<?php
declare(strict_types=1);

date_default_timezone_set('Asia/Kolkata');

const ADX_DB_DSN     = 'pgsql:host=localhost;port=5432;dbname=Custom_Cms_Admin';
const ADX_DB_USER    = 'postgres';
const ADX_DB_PASS    = 'Neel@1507';

const MAIN_DB_DSN    = 'pgsql:host=161.97.155.98;port=35433;dbname=pg_hksync';
const MAIN_DB_USER   = 'postgres';
const MAIN_DB_PASS   = 'AwMXhmx3pJJsfAwn';

const TELEGRAM_TOKEN = '8109713069:AAH9RrXDV--shcZfL6nqexRblvSLn_4gMQA';
const TELEGRAM_CHAT  = '617809651';

$logFile = __DIR__ . '/adx_report_' . date('Y_m_d_H') . '.log';

require __DIR__ . '/vendor/autoload.php';

use Google\AdsApi\AdManager\v202502\ReportQuery;
use Google\AdsApi\AdManager\v202502\Column;
use Google\AdsApi\AdManager\v202502\Dimension;
use Google\AdsApi\AdManager\v202502\ExportFormat;
use Google\AdsApi\AdManager\v202502\ReportJob;
use Google\AdsApi\AdManager\v202502\ReportQueryAdUnitView;
use Google\AdsApi\AdManager\v202502\DateRangeType;
use Google\AdsApi\AdManager\Util\v202502\ReportDownloader;
use Google\AdsApi\Common\OAuth2TokenBuilder;
use Google\AdsApi\AdManager\AdManagerSessionBuilder;
use Google\AdsApi\AdManager\v202502\ServiceFactory;

/**
 * Append a message to the log file.
 */
function logMsg(string $msg): void {
    global $logFile;
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - {$msg}\n", FILE_APPEND);
}

/**
 * Send a Telegram notification.
 */
function sendTelegramMessage(string $message): void {
    $url  = "https://api.telegram.org/bot" . TELEGRAM_TOKEN . "/sendMessage";
    $data = [
        'chat_id'    => TELEGRAM_CHAT,
        'text'       => $message,
        'parse_mode' => 'HTML',
    ];
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($data),
        CURLOPT_TIMEOUT        => 30,
    ]);
    curl_exec($ch);
    curl_close($ch);
}

$useYesterday = in_array('--yesterday', $argv, true) || in_array('yesterday', $argv, true);
$dateLabel    = $useYesterday ? 'Yesterday' : 'Today';

sendTelegramMessage("🚀 Cron started for {$dateLabel} at " . date('Y-m-d H:i:s'));

try {
    // 1) Connect to Postgres for adx_reports
    $pdo = new PDO(ADX_DB_DSN, ADX_DB_USER, ADX_DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    // 2) Connect to ap_main to fetch media_buyer
    $apPdo = new PDO(MAIN_DB_DSN, MAIN_DB_USER, MAIN_DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    $campaignStmt = $apPdo->prepare(
        'SELECT media_buyer FROM campaigns WHERE website_blog_id = :blog_id LIMIT 1'
    );

    // 3) Authenticate & prepare Ad Manager report
    $oAuth2        = (new OAuth2TokenBuilder())->fromFile()->build();
    $session       = (new AdManagerSessionBuilder())
                        ->fromFile()
                        ->withOAuth2Credential($oAuth2)
                        ->build();
    $reportService = (new ServiceFactory())->createReportService($session);

    $query = (new ReportQuery())
        ->setDimensions([
            Dimension::DATE,
            Dimension::CUSTOM_CRITERIA,
            Dimension::CUSTOM_TARGETING_VALUE_ID
        ])
        ->setColumns([
            Column::TOTAL_PROGRAMMATIC_ELIGIBLE_AD_REQUESTS,
            Column::AD_EXCHANGE_LINE_ITEM_LEVEL_IMPRESSIONS,
            Column::AD_EXCHANGE_LINE_ITEM_LEVEL_AVERAGE_ECPM,
            Column::AD_EXCHANGE_LINE_ITEM_LEVEL_REVENUE,
            Column::AD_EXCHANGE_LINE_ITEM_LEVEL_CLICKS,
        ])
        ->setAdUnitView(ReportQueryAdUnitView::HIERARCHICAL)
        ->setReportCurrency('USD')
        ->setDateRangeType($useYesterday ? DateRangeType::YESTERDAY : DateRangeType::TODAY);

    $job        = $reportService->runReportJob((new ReportJob())->setReportQuery($query));
    $downloader = new ReportDownloader($reportService, $job->getId());

    if (!$downloader->waitForReportToFinish()) {
        throw new RuntimeException('Ad Manager report timed out.');
    }

    // 4) Download + parse CSV
    $stream   = $downloader->downloadReport(ExportFormat::CSV_DUMP);
    $rawGzip = $stream->getContents();
    $csv     = gzdecode($rawGzip);
    if ($csv === false) {
        throw new RuntimeException('Failed to decompress Ad Manager report.');
    }

    $lines  = explode("\n", trim($csv));
    $header = str_getcsv(array_shift($lines));
    $idx    = array_flip($header);

    $rows = [];
    foreach ($lines as $line) {
        if ($line === '') {
            continue;
        }
        $r = str_getcsv($line);

        // extract blog_id
        $customCriteria = $r[$idx['Dimension.CUSTOM_CRITERIA']];
        $blogId = null;
        if (preg_match('/_(\d+)$/', $customCriteria, $m)) {
            $blogId = (int)$m[1];
        }

        // lookup media_buyer
        $mediaBuyer = null;
        if ($blogId !== null) {
            $campaignStmt->execute([':blog_id' => $blogId]);
            $mediaBuyer = $campaignStmt->fetchColumn() ?: null;
        }

        $rows[] = [
            'report_date'               => $r[$idx['Dimension.DATE']],
            'custom_criteria'           => $customCriteria,
            'blog_id'                   => $blogId,
            'media_buyer'               => $mediaBuyer,
            'custom_targeting_value_id' => $r[$idx['Dimension.CUSTOM_TARGETING_VALUE_ID']],
            'ad_requests'               => (int)$r[$idx['Column.TOTAL_PROGRAMMATIC_ELIGIBLE_AD_REQUESTS']],
            'impressions'               => (int)$r[$idx['Column.AD_EXCHANGE_LINE_ITEM_LEVEL_IMPRESSIONS']],
            'average_ecpm'              => round((float)$r[$idx['Column.AD_EXCHANGE_LINE_ITEM_LEVEL_AVERAGE_ECPM']] / 1_000_000, 2),
            'revenue'                   => round((float)$r[$idx['Column.AD_EXCHANGE_LINE_ITEM_LEVEL_REVENUE']] / 1_000_000, 2),
            'clicks'                    => (int)$r[$idx['Column.AD_EXCHANGE_LINE_ITEM_LEVEL_CLICKS']],
            'currency_code'             => 'USD',
        ];
    }

    if (empty($rows)) {
        sendTelegramMessage("ℹ️ No data for {$dateLabel}, exiting.");
        exit(0);
    }

    // 5) Upsert into adx_reports
    $pdo->beginTransaction();

    $pdo->exec(<<<'SQL'
CREATE TEMP TABLE tmp_adx_reports (
  report_date DATE,
  custom_criteria TEXT,
  blog_id INT,
  media_buyer VARCHAR(20),
  custom_targeting_value_id TEXT,
  ad_requests INT,
  impressions INT,
  average_ecpm NUMERIC(10,2),
  revenue NUMERIC(12,2),
  clicks INT,
  currency_code TEXT
);
SQL
    );

    $insert = $pdo->prepare(<<<'SQL'
INSERT INTO tmp_adx_reports (
  report_date, custom_criteria, blog_id, media_buyer,
  custom_targeting_value_id,
  ad_requests, impressions, average_ecpm, revenue, clicks, currency_code
) VALUES (
  :report_date, :custom_criteria, :blog_id, :media_buyer,
  :custom_targeting_value_id,
  :ad_requests, :impressions, :average_ecpm, :revenue, :clicks, :currency_code
);
SQL
    );
    foreach ($rows as $r) {
        $insert->execute($r);
    }

    $pdo->exec(<<<'SQL'
INSERT INTO adx_reports (
  report_date, custom_criteria, blog_id, media_buyer,
  custom_targeting_value_id,
  ad_requests, impressions, average_ecpm, revenue, clicks,
  currency_code, created_at, updated_at
)
SELECT
  report_date, custom_criteria, blog_id, media_buyer,
  custom_targeting_value_id,
  ad_requests, impressions, average_ecpm, revenue, clicks,
  currency_code, NOW(), NOW()
FROM tmp_adx_reports
ON CONFLICT (report_date, custom_criteria) DO UPDATE
SET
  blog_id                   = EXCLUDED.blog_id,
  media_buyer               = EXCLUDED.media_buyer,
  custom_targeting_value_id = EXCLUDED.custom_targeting_value_id,
  ad_requests               = EXCLUDED.ad_requests,
  impressions               = EXCLUDED.impressions,
  average_ecpm              = EXCLUDED.average_ecpm,
  revenue                   = EXCLUDED.revenue,
  clicks                    = EXCLUDED.clicks,
  currency_code             = EXCLUDED.currency_code,
  updated_at                = NOW();
SQL
    );

    $pdo->commit();
    sendTelegramMessage("✅ Cron completed for {$dateLabel} at " . date('Y-m-d H:i:s') . " — " . count($rows) . " rows");

    // Explicitly close connections & statements
    $campaignStmt = null;
    $apPdo        = null;
    $pdo          = null;

} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    logMsg('Error: ' . $e->getMessage());
    sendTelegramMessage('❌ Error: ' . htmlspecialchars($e->getMessage()));
    $campaignStmt = null;
    $apPdo        = null;
    $pdo          = null;

    exit(1);
}
