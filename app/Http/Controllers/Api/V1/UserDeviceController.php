<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\UserDevice;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Http\Request;

class UserDeviceController extends Controller
{
    public function index(Request $request)
    {
        $devices = $request->user()->devices()->orderByDesc('last_active_at')->get()
            ->map(fn ($d) => ['id' => $d->id, 'device_name' => $d->device_name, 'browser' => $d->browser, 'location' => $d->location, 'last_active_at' => $d->last_active_at, 'is_current' => $d->is_current]);
        return response()->json(['data' => $devices]);
    }

    public function destroy(Request $request, UserDevice $device)
    {
        abort_unless($device->user_id === $request->user()->id, 403);
        abort_if($device->is_current, 422, 'Cannot revoke current device.');
        if ($device->token) { PersonalAccessToken::findToken($device->token)?->delete(); }
        $device->delete();
        return response()->json(['message' => 'Device revoked.']);
    }
}
