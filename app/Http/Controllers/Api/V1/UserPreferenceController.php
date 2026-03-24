<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\UserPreference;
use Illuminate\Http\Request;

class UserPreferenceController extends Controller
{
    public function show(Request $request)
    {
        $prefs = UserPreference::firstOrCreate(
            ['user_id' => $request->user()->id],
            ['theme' => 'light', 'language' => 'ar', 'direction' => 'rtl']
        );
        return response()->json(['data' => $prefs]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'theme' => 'sometimes|in:light,dark',
            'language' => 'sometimes|in:en,ar',
        ]);

        if (isset($validated['language'])) {
            $validated['direction'] = $validated['language'] === 'ar' ? 'rtl' : 'ltr';
        }

        $prefs = UserPreference::updateOrCreate(['user_id' => $request->user()->id], $validated);
        return response()->json(['data' => $prefs, 'message' => 'Preferences updated.']);
    }
}
