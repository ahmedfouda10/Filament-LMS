<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\LessonResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LessonResourceApiController extends Controller
{
    public function store(Request $request, Lesson $lesson)
    {
        abort_unless($lesson->module->course->instructor_id === $request->user()->id, 403);
        $validated = $request->validate(['title' => 'required|string|max:255', 'file' => 'required|file|mimes:pdf,pptx,doc,docx,xlsx|max:20480']);
        $path = $request->file('file')->store('lesson-resources', 'public');
        $resource = LessonResource::create([
            'lesson_id' => $lesson->id, 'title' => $validated['title'], 'file_url' => $path,
            'file_type' => $request->file('file')->getClientOriginalExtension(), 'file_size' => $request->file('file')->getSize(),
            'is_downloadable' => true, 'sort_order' => $lesson->resources()->count(),
        ]);
        return response()->json(['data' => $resource, 'message' => 'Resource uploaded.'], 201);
    }

    public function update(Request $request, LessonResource $resource)
    {
        abort_unless($resource->lesson->module->course->instructor_id === $request->user()->id, 403);
        $resource->update($request->validate(['title' => 'sometimes|string|max:255', 'is_downloadable' => 'sometimes|boolean']));
        return response()->json(['data' => $resource, 'message' => 'Resource updated.']);
    }

    public function destroy(Request $request, LessonResource $resource)
    {
        abort_unless($resource->lesson->module->course->instructor_id === $request->user()->id, 403);
        Storage::disk('public')->delete($resource->file_url);
        $resource->delete();
        return response()->json(['message' => 'Resource deleted.']);
    }
}
