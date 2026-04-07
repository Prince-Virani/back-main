<?php

namespace App\Http\Controllers;

use App\Models\Commonpage;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommonPageController extends Controller
{
    public function create()
    {
        $websites = Website::where('status_flag', 0)->get();
        return view('pages/Commonpages.create', compact('websites'));
    }
    public function index(Request $request)
    {
        try {
            $query = Commonpage::select('id', 'page_name', 'status_flag', 'website_id', 'slug', 'updated_at')
                ->with(['website:id,website_name']);

            if ($request->has('website_id') && $request->website_id != 0) {
                $query->where('website_id', $request->website_id);
            }

            if ($request->has('status_flag') && $request->status_flag !== 'null' && $request->status_flag != 2) {
                $query->where('status_flag', $request->status_flag);
            }

            if ($request->has('search') && !empty($request->search)) {
                $query->where('page_name', 'like', '%' . $request->search . '%');
            }

            return response()->json($query->latest('updated_at')->paginate(10));
        } catch (\Exception $e) {
            Log::error('Error fetching Common pages: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return back()->with('error', 'Error fetching Common pages: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'page_name' => 'required|string|max:255',
            'content' => 'nullable|string',
            'website_id' => 'required|exists:websites,id',
            'slug' => 'required|string|max:255',
        ]);


        Commonpage::create($validated);
        return redirect()->route('Commonpages.index')->with('success', 'Commonpages created successfully!');
    }

    public function edit(Commonpage $Commonpage)
    {

        $websites = Website::where('status_flag', 0)->get();
        return view('pages/Commonpages.create', compact('Commonpage', 'websites')); // Reuses the same form
    }

    public function update(Request $request, Commonpage $Commonpage)
    {
        $validated = $request->validate([
            'page_name' => 'required|string|max:255',
            'content' => 'nullable|string',
            'website_id' => 'required|exists:websites,id',
            'slug' => 'required|string|max:255',
        ]);


        $Commonpage->update($validated);
        return redirect()->route('Commonpages.index')->with('success', 'Page updated successfully!');
    }

    public function destroy(Commonpage $Commonpage, Request $request,)
    {

        $websiteId = $Commonpage->website_id;
        $pageId = $Commonpage->id;
        $newStatus = $request->input('status_flag');
        DB::statement("CALL update_Common_page_status(?, ?,?)", [$websiteId, $pageId, $newStatus]);
        return redirect()->route('Commonpages.index')->with('success', 'Commonpages status updated successfully.');
    }
}
