<?php

namespace App\Http\Controllers;

use App\Models\FirestoreAppSetting;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

final class FirestoreAppSettingController extends Controller
{
    public function index()
    {
        $settings = FirestoreAppSetting::orderBy('id', 'desc')->paginate(15);
        return view('pages.firestore_app_settings.index', compact('settings'));
    }

    public function create()
    {
        $apps = Application::orderBy('application_name')->get(['application_name', 'package_name']);
        return view('pages.firestore_app_settings.create', compact('apps'));
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        DB::beginTransaction();
        try {
            $data['status_flag'] = $request->has('status_flag');

            if ($request->hasFile('credentials_file')) {
                $uploaded = $request->file('credentials_file');
                $raw = file_get_contents($uploaded->getRealPath());
                if (json_decode($raw) === null && json_last_error() !== JSON_ERROR_NONE) {
                    throw new \RuntimeException('Invalid JSON file.');
                }
                $data['credentials_json'] = $raw;
                $data['credentials_filename'] = $uploaded->getClientOriginalName();
            }

            FirestoreAppSetting::create($data);
            DB::commit();
            return redirect()->route('firestore-app-settings.index')->with('success', 'Created.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function edit(FirestoreAppSetting $firestore_app_setting)
    {
        $apps = Application::orderBy('application_name')->get(['application_name', 'package_name']);
        return view('pages.firestore_app_settings.create', [
            'apps' => $apps,
            'firestore_setting' => $firestore_app_setting,
        ]);
    }

    public function update(Request $request, FirestoreAppSetting $firestore_app_setting)
    {
        $data = $request->validate($this->rules($firestore_app_setting->id));

        DB::beginTransaction();
        try {
            $data['status_flag'] = $request->has('status_flag');

            if ($request->hasFile('credentials_file')) {
                $uploaded = $request->file('credentials_file');
                $raw = file_get_contents($uploaded->getRealPath());
                if (json_decode($raw) === null && json_last_error() !== JSON_ERROR_NONE) {
                    throw new \RuntimeException('Invalid JSON file.');
                }
                $data['credentials_json'] = $raw;
                $data['credentials_filename'] = $uploaded->getClientOriginalName();
            } else {
                unset($data['credentials_json'], $data['credentials_filename']);
            }

            $firestore_app_setting->update($data);
            DB::commit();
            return redirect()->route('firestore-app-settings.index')->with('success', 'Updated.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function toggle(Request $request, FirestoreAppSetting $firestore_app_setting)
    {
        try {
            $firestore_app_setting->update(['status_flag' => $request->boolean('status_flag')]);
            return redirect()->back()->with('success', 'Status updated.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    protected function rules(?int $id = null): array
    {
        return [
            'application_package' => ['required', 'string', Rule::unique('firestore_app_settings', 'application_package')->ignore($id)],
            'firebase_project_id' => ['required', 'string', 'max:190'],
            'collection_name' => ['required', 'string', 'max:190'],
            'document_name' => ['required', 'string', 'max:190'],
            'field_name' => ['nullable', 'string', 'max:190'], // new field
            'credentials_file' => ['nullable', 'file', 'mimes:json', 'mimetypes:application/json,text/plain,application/octet-stream', 'max:10240'],
            'status_flag' => ['nullable'],
        ];
    }
}
