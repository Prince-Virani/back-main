<?php

namespace App\Http\Controllers;

use App\Models\APIKey;
use Illuminate\Http\Request;
use App\Models\Application;
use Illuminate\Support\Facades\Log;

class APIKeyController extends Controller
{

    public function index()
    {
        $apiKeys = APIKey::where('status_flag', 0)->paginate(10); 
        return view('pages.api_keys.index', compact('apiKeys'));
    }

    public function toggleStatus(Request $request, APIKey $apiKey)
    {
        $request->validate([
            'package_name' => 'required|string|max:255',
            'apikey' => 'required|string',
            'status_flag' => 'required|in:0,1'
        ]);

        if ($request->status_flag == 1) {
            $app = Application::where('package_name', $request->package_name)->first();

            if (!$app) {
                return response()->json(['error' => 'No matching application found.'], 404);
            }

            // Append key if not already present
            $existingKeys = $app->api_keys ? explode(',', $app->api_keys) : [];

            if (!in_array($request->apikey, $existingKeys)) {
                $existingKeys[] = $request->apikey;
            }

            $app->api_keys = implode(',', $existingKeys);
            $app->save();

            // Delete the activated key
            $apiKey->delete();

            return response()->json(['success' => true, 'message' => 'API key activated and removed from pending list.']);
        }

        return response()->json(['error' => 'Deactivation is not supported.'], 400);
    }
}
