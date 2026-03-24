<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\UserNotificationPreference;
use Illuminate\Http\Request;

class NotificationPreferenceController extends Controller
{
    public function show(Request $request)
    {
        $prefs = UserNotificationPreference::firstOrCreate(
            ['user_id' => $request->user()->id],
            ['course_updates' => true, 'marketing' => false, 'account_security' => true]
        );
        return response()->json(['data' => $prefs]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate(['course_updates' => 'boolean', 'marketing' => 'boolean', 'account_security' => 'boolean']);
        $prefs = UserNotificationPreference::updateOrCreate(['user_id' => $request->user()->id], $validated);
        return response()->json(['data' => $prefs, 'message' => 'Preferences updated.']);
    }
}
