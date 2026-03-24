<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\OfflineDownload;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OfflineDownloadController extends Controller
{
    public function generateToken(Request $request, Course $course)
    {
        abort_unless(Enrollment::where('user_id', $request->user()->id)->where('course_id', $course->id)->exists(), 403, 'You must be enrolled in this course.');

        $validated = $request->validate(['lesson_id' => 'nullable|exists:lessons,id']);

        $active = OfflineDownload::where('user_id', $request->user()->id)->where('expires_at', '>', now())->count();
        abort_if($active >= 5, 422, 'Maximum 5 active downloads allowed.');

        $download = OfflineDownload::create([
            'user_id' => $request->user()->id, 'course_id' => $course->id,
            'lesson_id' => $validated['lesson_id'] ?? null, 'file_size_bytes' => 0,
            'download_token' => Str::random(64), 'expires_at' => now()->addHours(24),
        ]);

        return response()->json(['data' => ['download_url' => url("/api/v1/downloads/{$download->download_token}"), 'expires_at' => $download->expires_at->toISOString()]], 201);
    }

    public function index(Request $request)
    {
        $downloads = OfflineDownload::where('user_id', $request->user()->id)
            ->with('course:id,title,slug,image')->with('lesson:id,title')->latest()->paginate(10);

        return response()->json([
            'data' => $downloads->through(fn ($d) => [
                'id' => $d->id, 'course' => $d->course, 'lesson' => $d->lesson,
                'formatted_size' => $d->formatted_size, 'expires_at' => $d->expires_at,
                'downloaded_at' => $d->downloaded_at, 'is_expired' => $d->isExpired(),
            ]),
            'meta' => ['current_page' => $downloads->currentPage(), 'per_page' => $downloads->perPage(), 'total' => $downloads->total()],
        ]);
    }

    public function destroy(Request $request, OfflineDownload $download)
    {
        abort_unless($download->user_id === $request->user()->id, 403);
        $download->delete();
        return response()->json(['message' => 'Download removed.']);
    }

    public function download(string $token)
    {
        $download = OfflineDownload::where('download_token', $token)->firstOrFail();
        abort_if($download->isExpired(), 410, 'Download link has expired.');
        $download->update(['downloaded_at' => now()]);
        return response()->json(['message' => 'Stream started.']);
    }
}
