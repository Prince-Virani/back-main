<?php


namespace App\Http\Controllers;

use App\Models\Vertical;
use Illuminate\Http\Request;

class VerticalController extends Controller
{
    public function index()
    {
        $verticals = Vertical::paginate(5);
        return view('pages/verticals.index', compact('verticals'));
    }

    public function create()
    {
        return view('pages/verticals.create');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:verticals|max:255']);
        Vertical::create($request->all());
        return redirect()->route('verticals.index')->with('success', 'Vertical created successfully!');
    }

    public function edit(Vertical $vertical)
    {
        return view('pages/verticals.edit', compact('vertical'));
    }

    public function update(Request $request, Vertical $vertical)
{
    $request->validate(['name' => 'required|unique:verticals,name,' . $vertical->id]);
    $vertical->update(['name' => $request->name]);

    return response()->json(['success' => true, 'message' => 'Vertical updated successfully!']);
}

public function destroy(Vertical $vertical)
{
    $vertical->delete();
    return response()->json(['success' => true, 'message' => 'Vertical deleted successfully!']);
}
}
