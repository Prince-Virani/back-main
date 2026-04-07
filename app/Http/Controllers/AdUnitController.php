<?php

namespace App\Http\Controllers;

use App\Models\Website;
use App\Models\AdUnit;
use App\Models\AdPosition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdUnitController extends Controller
{
    public function index()
    {
       
        $websites = Website::all();

       
        foreach ($websites as $website) {
            $website->ad_units_count = AdUnit::where('website_id', $website->id)->count();
            $website->adUnits = AdUnit::where('website_id', $website->id)->get();
        }
        $adPositions = AdPosition::where('status_flag', 0)->get();

        return view('pages.ad-units.index', compact('websites', 'adPositions'));
       
       
    }
    public function toggleStatus(Request $request, $id)
    {
        $adUnit = AdUnit::where('adunit_id', $id)
            ->where('website_id', $request->website_id) 
            ->firstOrFail();

        $adUnit->status_flag = $request->has('status_flag');
        $adUnit->save();

        return response()->json(['success' => true]);
    }
    public function toggleLazy(Request $request, $id)
    {
        $adUnit = AdUnit::where('adunit_id', $id)
            ->where('website_id', $request->website_id) 
            ->firstOrFail();

        $adUnit->is_lazy = $request->has('is_lazy');
        $adUnit->save();

        return response()->json(['success' => true]);
    }
    public function destroy(Request $request, $id)
    {
    try {
        $adUnit = AdUnit::where('adunit_id', $id)
            ->where('website_id', $request->website_id) 
            ->firstOrFail();
        
        $adUnit->delete();
        
        return response()->json(['success' => true, 'message' => 'Ad unit deleted successfully']);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'error' => 'Ad unit not found or deletion failed'], 404);
    }
}
}
