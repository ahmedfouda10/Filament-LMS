<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\LessonNote;
use Illuminate\Http\Request;

class LessonNoteController extends Controller
{
    public function index(Request $request, Lesson $lesson)
    {
        return response()->json(['data' => LessonNote::where('user_id', $request->user()->id)->where('lesson_id', $lesson->id)->latest()->get()]);
    }

    public function store(Request $request, Lesson $lesson)
    {
        $validated = $request->validate(['content' => 'required|string|max:5000']);
        $note = LessonNote::create(['user_id' => $request->user()->id, 'lesson_id' => $lesson->id, 'content' => $validated['content']]);
        return response()->json(['data' => $note, 'message' => 'Note saved.'], 201);
    }

    public function update(Request $request, LessonNote $note)
    {
        abort_unless($note->user_id === $request->user()->id, 403);
        $note->update($request->validate(['content' => 'required|string|max:5000']));
        return response()->json(['data' => $note, 'message' => 'Note updated.']);
    }

    public function destroy(Request $request, LessonNote $note)
    {
        abort_unless($note->user_id === $request->user()->id, 403);
        $note->delete();
        return response()->json(['message' => 'Note deleted.']);
    }
}
