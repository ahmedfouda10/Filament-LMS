<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Setting;

class FeatureFlagController extends Controller
{
    public function index()
    {
        $flags = Setting::where('key', 'like', 'feature_%')->pluck('value', 'key')
            ->mapWithKeys(fn ($value, $key) => [str_replace('feature_', '', $key) => $value === 'true']);

        return response()->json(['data' => $flags]);
    }
}
