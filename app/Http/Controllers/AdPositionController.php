<?php

namespace App\Http\Controllers;

use App\Models\AdPosition; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdPositionController extends Controller
{
    public function index()
    {
        $adPositions = AdPosition::all(); 
        return view('pages/adpositions.index', compact('adPositions'));
    }

    public function create()
    {
        return view('pages/adpositions.create'); 
    }

    public function store(Request $request)
    {
        $request->validate([
            'position_name' => 'required|string|max:255',
        ]);

        AdPosition::create([
            'position_name' => $request->position_name,
        ]);

        return redirect()->route('adpositions.index')->with('success', 'Ad position added successfully!');
    }

    public function edit($id)
    {
        $adPosition = AdPosition::findOrFail($id);
        return view('pages/adpositions.create', compact('adPosition')); 
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'position_name' => 'required|string|max:255',
        ]);

        $adPosition = AdPosition::findOrFail($id);
        $adPosition->update([
            'position_name' => $request->position_name,
        ]);

        return redirect()->route('adpositions.index')->with('success', 'Ad position updated successfully!');
    }

    public function destroy($id, Request $request)
    {
        try {
            $adPosition = AdPosition::find($id); 
            $newStatus = $request->input('status_flag'); 
            DB::statement("CALL ad_positions_status(?, ?)", [$adPosition->id, $newStatus]);
            $message = ($newStatus == 1) ? 'Ad position deactivated successfully!' : 'Ad position activated successfully.';
            return redirect()->route('adpositions.index')->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Error updating ad position: ' . $e->getMessage());
        }
    }
}
