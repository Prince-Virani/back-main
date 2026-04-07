<?php

namespace App\Http\Controllers;

use App\Models\Theme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ThemeController extends Controller
{
    public function index()
    {
        $themes = Theme::paginate(10);
        return view('pages/themes.index', compact('themes'));
    }

    public function create()
    {
        return view('pages/themes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'themename' => 'required|unique:themes|max:255'
        ]);

        $themeName = $request->themename;
        
        $frontendBasePath = base_path('../WEBSYNC');
       

        $themeFolder  = $frontendBasePath . '/resources/views/themes/' . $themeName;
        $layoutFolder = $themeFolder . '/layouts';
        $cssFolder    = $frontendBasePath . '/public/themes/' . $themeName . '/css';

        if (file_exists($themeFolder) || file_exists($cssFolder)) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['themename' => 'Theme folder already exists on frontend.']);
        }

        try {
            if (!mkdir($themeFolder, 0755, true) ||
                !mkdir($layoutFolder, 0755, true) ||
                !mkdir($cssFolder, 0755, true)) {
                throw new \Exception("Failed to create one or more directories.");
            }

            $appStubPath  = resource_path('stubs/default-app-layout.blade.stub');
            $homeStubPath = resource_path('stubs/default-home.blade.stub');

            if (!file_exists($appStubPath) || !file_exists($homeStubPath)) {
                throw new \Exception("One or more stub files are missing.");
            }

            if (
                file_put_contents($layoutFolder . '/app.blade.php', file_get_contents($appStubPath)) === false ||
                file_put_contents($themeFolder . '/home.blade.php', file_get_contents($homeStubPath)) === false ||
                file_put_contents($cssFolder . '/style.css', "/* Custom styles for $themeName */") === false
            ) {
                throw new \Exception("Failed to write one or more theme files.");
            }

            Theme::create(['themename' => $themeName]);

            return redirect()->route('themes.index')->with('success', 'Theme folders created & theme stored successfully!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Theme creation failed: ' . $e->getMessage()]);
        }
    }

    public function edit(Theme $theme)
    {
        return view('pages/themes.edit', compact('theme'));
    }

    public function update(Request $request, Theme $theme)
    {
        $request->validate([
            'themename' => 'required|unique:themes,themename,' . $theme->id
        ]);

        $theme->update([
            'themename' => $request->themename
        ]);

        return response()->json(['success' => true, 'message' => 'Theme updated successfully!']);
    }

    public function destroy(Theme $theme)
    {
        try {
            $newStatus = $theme->status_flag == 0 ? 1 : 0;

            DB::statement("CALL update_theme_status(?, ?)", [$theme->id, $newStatus]);

            $message = $newStatus == 0
                ? 'Theme activated successfully!'
                : 'Theme deactivated successfully.';

            return response()->json(['message' => $message], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error updating theme: ' . $e->getMessage()
            ], 500);
        }
    }
}
