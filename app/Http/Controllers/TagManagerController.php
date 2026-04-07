<?php

namespace App\Http\Controllers;

use App\Models\TagManager;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TagManagerController extends Controller
{
    public function index()
    {
        $tagmanagers = TagManager::with('website')->paginate(15);
        return view('pages/tagmanagers.index', compact('tagmanagers'));
    }

    public function create()
    {
        $websites = Website::where('status_flag', 0)->get();
       
        return view('pages/tagmanagers.create', compact('websites'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'website_id' => 'required|exists:websites,id',
            'content' => 'required|string',
            
        ]);

        TagManager::create($request->all());
        return redirect()->route('tagmanagers.index')->with('success', 'Tag Manager saved!');
    }

    public function edit($id)
    {
        $tagmanager = TagManager::findOrFail($id);
        $websites = Website::where('status_flag', 0)->get();
      
        return view('pages/tagmanagers.create', compact('tagmanager', 'websites',));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'website_id' => 'required|exists:websites,id',
           'content' => 'required|string',
        ]);

        $tagmanager = TagManager::findOrFail($id);
        $tagmanager->update($request->all());
        return redirect()->route('tagmanagers.index')->with('success', 'Tag Manager updated!');
    }

    public function destroy($id, Request $request)
    {
        try {
            $TagManager = TagManager::find($id); 
            $newStatus = $request->input('status_flag');
            DB::statement("CALL Update_tag_manager_StatusFlag(?, ?)", [$TagManager->id, $newStatus]);
            $message = ($newStatus == 0) ? 'Tag Manager activated successfully!' : 'Tag Manager deactivated successfully.';
    
            return redirect()->route('tagmanagers.index')->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Error updating Tag Manager: ' . $e->getMessage());
        }
    }
}