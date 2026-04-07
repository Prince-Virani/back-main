<?php

namespace App\Http\Controllers;

use App\Models\AdTxt;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdTxtController extends Controller
{
    public function index()
    {
        $adstxt = AdTxt::with('website')->paginate(5);
        return view('pages/adstxt.index', compact('adstxt'));
    }

    public function create()
    {
        $websites = Website::where('status_flag', 0)->get();
        return view('pages/adstxt.create', compact('websites'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'website_id' => 'required|exists:websites,id',
            'content' => 'required|string',
        ]);

        $adtxt = AdTxt::create($request->all());
        $this->generateAdsTxtFile($adtxt->website_id);

        return redirect()->route('adstxt.index')->with('success', 'Ads txt saved!');
    }

    public function edit($id)
    {
        $adtxt = AdTxt::findOrFail($id);
        $websites = Website::where('status_flag', 0)->get();

        return view('pages/adstxt.create', compact('adtxt', 'websites'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'website_id' => 'required|exists:websites,id',
            'content' => 'required|string',
        ]);

        $adtxt = AdTxt::findOrFail($id);
        $adtxt->update($request->all());
        $this->generateAdsTxtFile($adtxt->website_id);

        return redirect()->route('adstxt.index')->with('success', 'Ads txt updated!');
    }

    public function destroy($id, Request $request)
    {
        try {
            $adtxt = AdTxt::findOrFail($id); 
            $newStatus = $request->input('status_flag');
            DB::statement("CALL Adstxt_Status(?, ?)", [$adtxt->id, $newStatus]);

            $message = ($newStatus == 0)
                ? 'Ads txt activated successfully!'
                : 'Ads txt deactivated successfully.';

            return redirect()->route('adstxt.index')->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Error updating ad: ' . $e->getMessage());
        }
    }

    private function generateAdsTxtFile($websiteId)
    {
        $website = Website::find($websiteId);
        if (!$website) return;

        $domain = $website->domain;

        $ads = AdTxt::where('website_id', $websiteId)
            ->where('status_flag', 0)
            ->pluck('content')
            ->toArray();

        $adsTxtContent = implode("\n", $ads);

        $folderPath = base_path('../WEBSYNC/public/sites/' . $domain);

        if (!is_dir($folderPath)) {
            mkdir($folderPath, 0777, true);
        }

        $filePath = $folderPath . '/ads.txt';
        file_put_contents($filePath, $adsTxtContent);
    }
}
