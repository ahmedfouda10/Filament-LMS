<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CertificateResource;
use App\Models\Certificate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $certificates = $request->user()
            ->certificates()
            ->with('course.instructor', 'course.category')
            ->orderByDesc('issued_at')
            ->get();

        return response()->json([
            'data' => CertificateResource::collection($certificates),
        ]);
    }

    public function download(int $id, Request $request): JsonResponse
    {
        $certificate = Certificate::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->with('course.instructor', 'user')
            ->firstOrFail();

        return response()->json([
            'data' => [
                'certificate_number' => $certificate->certificate_number,
                'student_name' => $certificate->user->name,
                'course_title' => $certificate->course->title,
                'instructor_name' => $certificate->course->instructor->name ?? null,
                'issued_at' => $certificate->issued_at,
                'expires_at' => $certificate->expires_at,
                'download_url' => url("/api/v1/certificates/{$certificate->id}/pdf"),
            ],
        ]);
    }
}
