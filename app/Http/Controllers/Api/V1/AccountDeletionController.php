<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Notification;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AccountDeletionController extends Controller
{
    public function destroy(Request $request)
    {
        $validated = $request->validate(['password' => 'required|string', 'reason' => 'nullable|string|max:1000']);
        $user = $request->user();

        abort_unless(Hash::check($validated['password'], $user->password), 422, 'Incorrect password.');
        abort_if($user->role === 'admin', 422, 'Admin accounts cannot be self-deleted.');

        return DB::transaction(function () use ($user, $validated) {
            Subscription::where('user_id', $user->id)->where('status', 'active')->update(['status' => 'cancelled', 'auto_renew' => false]);
            $user->tokens()->delete();
            $user->devices()->delete();
            if ($user->cart) { $user->cart->items()->delete(); $user->cart->delete(); }

            $anonEmail = "deleted_{$user->id}@anonymized.local";
            $user->update(['name' => 'Deleted User', 'email' => $anonEmail, 'phone' => null, 'avatar' => null, 'is_active' => false]);
            $user->socialAccounts()->delete();
            $user->preference()->delete();
            $user->notificationPreference()->delete();
            $user->delete();

            if ($validated['reason']) {
                ContactMessage::create(['name' => 'Account Deletion', 'email' => $anonEmail, 'subject' => 'Account Deletion Request', 'message' => $validated['reason'], 'is_read' => false]);
            }

            $admin = User::where('role', 'admin')->first();
            if ($admin) {
                Notification::create(['user_id' => $admin->id, 'title' => 'Account Deleted', 'body' => "User #{$user->id} deleted their account", 'type' => 'system', 'data' => ['deleted_user_id' => $user->id]]);
            }

            return response()->json(['message' => 'Account deleted successfully.']);
        });
    }
}
