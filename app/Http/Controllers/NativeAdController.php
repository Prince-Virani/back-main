<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NativeAd;
use App\Models\Application;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class NativeAdController extends Controller
{
    public function index(Request $request)
    {
        $packageNames    = NativeAd::select('packagename')->distinct()->pluck('packagename');
        $applicationList = Application::whereIn('package_name', $packageNames)
            ->orderBy('application_name')
            ->pluck('application_name', 'package_name');

        $query = NativeAd::query();
        if ($request->filled('packagename')) {
            $query->where('packagename', $request->packagename);
        }

        $ads = $query
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->appends($request->only('packagename'));

        return view('pages.native_ads.index', compact('ads', 'applicationList'));
    }


    public function create()
    {
        $applications = Application::select('application_name', 'package_name')
            ->orderBy('application_name')
            ->get();

        return view('pages.native_ads.create', compact('applications'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'packagename'       => 'required|string|max:255',
            'calltoactionlink'  => 'required|url|max:500',
            'title'             => 'required|string|max:255',
            'description'       => 'nullable|string',
            'buttontext'        => 'required|string|max:100',
            'media'             => 'required|image|mimes:jpeg,png,svg,webp|max:5120',
            'icon'              => 'required|image|mimes:jpeg,png,svg,webp|max:2048',
        ]);

        try {
            DB::beginTransaction();

            if ($request->hasFile('media') && $request->file('media')->isValid()) {
                $file      = $request->file('media');
                $origName  = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $cleanName = Str::slug($origName) . '_' . time();
                $thumbImg  = Image::make($file)->encode('webp', 80);
                $thumbPath = "native-ads/media/thumbs/{$cleanName}.webp";
                $result    = Storage::disk('s3')->put($thumbPath, (string) $thumbImg);
                if (! $result) {
                    throw new \Exception("Failed to upload media to S3 at path {$thumbPath}");
                }
                $data['mediaurl'] = rtrim(config('app.contabo_s3_base_url'), '/') . '/' . ltrim($thumbPath, '/');
            }

            if ($request->hasFile('icon') && $request->file('icon')->isValid()) {
                $iconFile  = $request->file('icon');
                $origIcon  = pathinfo($iconFile->getClientOriginalName(), PATHINFO_FILENAME);
                $cleanIcon = Str::slug($origIcon) . '_' . time();
                $extIcon   = $iconFile->getClientOriginalExtension();
                $iconImg   = Image::make($iconFile)->fit(100, 100)->encode($extIcon, 80);
                $iconPath  = "native-ads/icons/{$cleanIcon}.{$extIcon}";
                $result    = Storage::disk('s3')->put($iconPath, (string) $iconImg);
                if (! $result) {
                    throw new \Exception("Failed to upload icon to S3 at path {$iconPath}");
                }
                $data['icon'] = rtrim(config('app.contabo_s3_base_url'), '/') . '/' . ltrim($iconPath, '/');
            }

            NativeAd::create([
                'packagename'      => $data['packagename'],
                'calltoactionlink' => $data['calltoactionlink'],
                'title'            => $data['title'],
                'description'      => $data['description'] ?? '',
                'buttontext'       => $data['buttontext'],
                'mediaurl'         => $data['mediaurl'] ?? null,
                'icon'             => $data['icon'] ?? null,
            ]);

            DB::commit();

            return redirect()
                ->route('native_ads.index')
                ->with('success', 'Native ad created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Failed to create native ad: ' . $e->getMessage());
        }
    }
    public function toggleStatus(Request $request, NativeAd $native_ad)
    {
        try {
            $native_ad->status_flag = $native_ad->status_flag ? 0 : 1;
            $native_ad->save();
            return response()->json([
                'success'      => true,
                'status_flag'  => $native_ad->status_flag
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
