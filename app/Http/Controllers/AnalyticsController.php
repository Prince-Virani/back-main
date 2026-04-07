<?php

namespace App\Http\Controllers;

use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\RunReportRequest;
use Google\Analytics\Data\V1beta\RunReportResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Illuminate\Http\Request;
use App\Models\Website;
//use Illuminate\Pagination\LengthAwarePaginator;
use Exception;

class AnalyticsController extends Controller
{
    public function index()
    {
        $websites = Website::where('status_flag', 0)->get();
        return view('pages/analytics.index', compact('websites'));  
    }
    public function fetchGaData(Request $request)
    {
      try {
            $site = Website::where('ga_property_id', $request->ga_property_id)->firstOrFail();
            $googlecredentialpath = config('app.google.credentials_path') . "/{$site->id}/" . config('app.google.service_file_name');
            $propertyId = $request->input('ga_property_id');     
            if (!$propertyId) {
                return response()->json(['error' => 'Property ID is required'], 400);
            }
            $startDate = $request->input('start_date', now()->toDateString());
            $endDate = $request->input('end_date', now()->toDateString());
            
            $gaData = [];
            $errors = [];
            // $perPage = 10;
            // $currentPage = LengthAwarePaginator::resolveCurrentPage();
            // $offset = ($currentPage - 1) * $perPage;

       
           $client = new BetaAnalyticsDataClient([
                 'credentials' => $googlecredentialpath,
           ]);

            $gaRequest = (new RunReportRequest())
                ->setProperty('properties/' . $propertyId)
                ->setDateRanges([
                    new DateRange([
                        'start_date' => $startDate,
                         'end_date' => $endDate,
                    ])
                ])
                ->setDimensions([
                    new Dimension(['name' => 'pagePath']),
                ])
                ->setMetrics([
                    new Metric(['name' => 'screenPageViews']),
                    new Metric(['name' => 'activeUsers']),
                    new Metric(['name' => 'userEngagementDuration']),
                    new Metric(['name' => 'totalRevenue']),
                    new Metric(['name' => 'eventCount']),
                    new Metric(['name' => 'screenPageViewsPerUser']),
                    new Metric(['name' => 'publisherAdClicks']),
                    new Metric(['name' => 'publisherAdImpressions']),
                ]);

            $response = $client->runReport($gaRequest);
            $allData = $this->processReportResponse($response);
            
            $gaData = collect($allData);

            // $items = array_slice($allData, $offset, $perPage);
            // $gaData = new LengthAwarePaginator($items, count($allData), $perPage, $currentPage, [
            //     'path' => FacadesRequest::url(),
            //     'query' => $request->query(),
            // ]);
        } catch (Exception $e) {
            $errors[] = '⚠️ Failed to load analytics data: ' . $e->getMessage();
        }

        return response()->json([
            'gaData' => $gaData,
            'errors' => $errors,
        ]);
    }

    protected function processReportResponse(RunReportResponse $response): array
    {
        $gaData = [];

        foreach ($response->getRows() as $row) {
            $pageTitle = $row->getDimensionValues()[0]->getValue();

            $views = $row->getMetricValues()[0]->getValue();
            $activeUsers = $row->getMetricValues()[1]->getValue();
            $userEngagementDuration = $row->getMetricValues()[2]->getValue();
            $totalRevenue = $row->getMetricValues()[3]->getValue();
            $eventCount = $row->getMetricValues()[4]->getValue();
            $screenPageViewsPerUser = $row->getMetricValues()[5]->getValue();
            $publisherAdClicks = $row->getMetricValues()[6]->getValue();
            $publisherAdImpressions = $row->getMetricValues()[7]->getValue();

            $averageEngagementTime = $activeUsers > 0 ? $userEngagementDuration / $activeUsers : 0;
            $payout = $publisherAdClicks > 0 ? $totalRevenue / $publisherAdClicks : 0;

            $normalizedPath = str_replace('/', '', $pageTitle);
            $localPage = DB::table('posts')->where('paramlink', $normalizedPath)->first();

            if ($localPage) {
                $gaData[] = [
                    'page_path' => $pageTitle,
                    'totalRevenue' => round($totalRevenue, 2),
                    'views' => $views,
                    'users' => $activeUsers,
                    'engagement' => round($averageEngagementTime, 2),
                    'eventCount' => $eventCount,
                    'screenPageViewsPerUser' => round($screenPageViewsPerUser,2),
                    'Currency' => '$',
                    'publisherAdClicks' => $publisherAdClicks,
                    'publisherAdImpressions' => $publisherAdImpressions,
                    'payout' => round($payout,2),
                ];
            }
        }

        return $gaData;
    }
}
