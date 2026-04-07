<?php

namespace App\Http\Controllers;

use App\Models\Link;
use App\Models\FirestoreAppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LinkController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'status'        => ['nullable', Rule::in(['0','1'])],
            'search'        => ['nullable','string','max:2000'],
            'package_name'  => ['nullable','string','max:255'],
        ]);
    
        $activePackages = FirestoreAppSetting::query()
            ->where('status_flag', 1)
            ->pluck('application_package')
            ->all();
    
        $packages = FirestoreAppSetting::query()
            ->where('status_flag', 1)
            ->orderBy('application_package')
            ->get(['application_package']);
    
        $links = Link::query()
            ->whereIn('package_name', $activePackages)
            ->when($request->filled('status'), fn ($q) => $q->where('status_flag', (int) $request->status))
            ->when($request->filled('search'), fn ($q) => $q->where('url', 'like', '%'.trim($request->search).'%'))
            ->when($request->filled('package_name'), fn ($q) => $q->where('package_name', $request->package_name))
            ->orderByDesc('id')
            ->paginate(10)
            ->appends($request->query());
    
        return view('pages.links.index', compact('links','packages'));
    }
    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
            'url'           => ['required','url','max:2000'],
            'counter'       => ['nullable','integer','min:0'],
            'status_flag'   => ['required','integer', Rule::in([0,1])],
            'package_name'  => [
                'required','string','max:255',
                Rule::exists('firestore_app_settings','application_package')->where(fn($q)=>$q->where('status_flag',1)),
            ],
        ]);

        if ($v->fails()) {
            return redirect()->route('links.index')
                ->withErrors($v, 'create')
                ->withInput()
                ->with('openModal', 'create');
        }

        $data = $v->validated();
        $data['counter'] = $data['counter'] ?? 0;

        Link::create($data);

        return redirect()->route('links.index')->with('success', 'Link created.');
    }

    public function update(Request $request, Link $link)
    {
        $v = Validator::make($request->all(), [
            'url'           => ['required','url','max:2000'],
            'counter'       => ['required','integer','min:0'],
            'status_flag'   => ['required','integer', Rule::in([0,1])],
            'package_name'  => [
                'required','string','max:255',
                Rule::exists('firestore_app_settings','application_package'),
            ],
        ]);

        if ($v->fails()) {
            return redirect()->route('links.index')
                ->withErrors($v, 'edit')
                ->withInput()
                ->with('openModal', 'edit')
                ->with('edit_id', $link->id);
        }

        $link->update($v->validated());

        return redirect()->route('links.index')->with('success', 'Link updated.');
    }

    public function destroy(Request $request, Link $link)
    {
        $link->delete();

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->route('links.index')->with('success', 'Link deleted.');
    }

    public function toggle(Request $request, Link $link)
    {
        $link->update(['status_flag' => $link->status_flag ? 0 : 1]);

        if ($request->expectsJson()) {
            return response()->json([
                'ok'   => true,
                'item' => [
                    'id'          => $link->id,
                    'status_flag' => $link->status_flag,
                ],
            ]);
        }

        return back()->with('success', 'Status updated.');
    }
}
