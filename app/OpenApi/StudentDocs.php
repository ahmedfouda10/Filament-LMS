<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

class StudentDocs
{
    // ==================== DASHBOARD ====================

    #[OA\Get(
        path: '/student/dashboard',
        summary: 'Get student dashboard overview',
        description: 'Returns summary statistics and up to 3 in-progress courses for "continue learning".',
        tags: ['Student - Dashboard'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Dashboard data', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'object', properties: [
                    new OA\Property(property: 'enrolled_courses', type: 'integer', example: 5),
                    new OA\Property(property: 'active_subscriptions', type: 'integer', example: 1),
                    new OA\Property(property: 'completed_courses', type: 'integer', example: 2),
                    new OA\Property(property: 'certificates_earned', type: 'integer', example: 2),
                    new OA\Property(property: 'continue_learning', type: 'array', items: new OA\Items(type: 'object'), description: 'Up to 3 most recently active enrollments'),
                ])]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function dashboard() {}

    // ==================== MY COURSES ====================

    #[OA\Get(
        path: '/student/courses',
        summary: 'List enrolled courses',
        description: 'Returns paginated list of enrolled courses with filtering and search.',
        tags: ['Student - Courses'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'filter', in: 'query', required: false, description: 'Filter by status', schema: new OA\Schema(type: 'string', enum: ['all', 'in-progress', 'completed'], default: 'all')),
            new OA\Parameter(name: 'search', in: 'query', required: false, description: 'Search by course title', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 10)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Enrolled courses with pagination', content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object')),
                    new OA\Property(property: 'meta', type: 'object'),
                ]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function courseIndex() {}

    #[OA\Get(
        path: '/student/courses/{id}/player',
        summary: 'Get course player data',
        description: 'Returns full course content for the player, including modules, lessons, and completion status. Student must be enrolled.',
        tags: ['Student - Courses'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Course ID', schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Course player data with modules, lessons, and completion status', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'object', properties: [
                    new OA\Property(property: 'course', type: 'object'),
                    new OA\Property(property: 'enrollment', type: 'object'),
                    new OA\Property(property: 'modules', type: 'array', items: new OA\Items(type: 'object')),
                    new OA\Property(property: 'completed_lesson_ids', type: 'array', items: new OA\Items(type: 'integer')),
                ])]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Enrollment not found'),
        ]
    )]
    public function coursePlayer() {}

    // ==================== PROGRESS ====================

    #[OA\Post(
        path: '/student/lessons/{id}/complete',
        summary: 'Mark lesson as complete',
        description: 'Marks a lesson as completed. Recalculates progress. If 100% and all quizzes passed, generates certificate.',
        tags: ['Student - Progress'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Lesson ID', schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Lesson completed', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'object', properties: [
                    new OA\Property(property: 'lesson_id', type: 'integer', example: 10),
                    new OA\Property(property: 'is_completed', type: 'boolean', example: true),
                    new OA\Property(property: 'progress_percentage', type: 'number', example: 75.0),
                    new OA\Property(property: 'total_lessons', type: 'integer', example: 20),
                    new OA\Property(property: 'completed_lessons', type: 'integer', example: 15),
                    new OA\Property(property: 'course_completed', type: 'boolean', example: false),
                ])]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Not enrolled in course'),
            new OA\Response(response: 404, description: 'Lesson not found'),
        ]
    )]
    public function lessonComplete() {}

    #[OA\Get(
        path: '/student/courses/{id}/progress',
        summary: 'Get course progress details',
        description: 'Returns detailed progress including completed lesson list for a specific course.',
        tags: ['Student - Progress'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Course ID', schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Progress details', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'object', properties: [
                    new OA\Property(property: 'course_id', type: 'integer'),
                    new OA\Property(property: 'progress_percentage', type: 'number', example: 60.0),
                    new OA\Property(property: 'total_lessons', type: 'integer', example: 10),
                    new OA\Property(property: 'completed_count', type: 'integer', example: 6),
                    new OA\Property(property: 'completed_at', type: 'string', format: 'date-time', nullable: true),
                    new OA\Property(property: 'lesson_completions', type: 'array', items: new OA\Items(type: 'object')),
                ])]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function courseProgress() {}

    // ==================== CERTIFICATES ====================

    #[OA\Get(
        path: '/student/certificates',
        summary: 'List student certificates',
        description: 'Returns all certificates earned by the authenticated student.',
        tags: ['Student - Certificates'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Certificates list', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object'))]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function certificateIndex() {}

    #[OA\Get(
        path: '/student/certificates/{id}/download',
        summary: 'Download certificate',
        description: 'Returns certificate details with download URL. Only accessible by the certificate owner.',
        tags: ['Student - Certificates'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Certificate ID', schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Certificate details with download URL', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'object', properties: [
                    new OA\Property(property: 'certificate_number', type: 'string', example: 'CERT-AB12CD34'),
                    new OA\Property(property: 'student_name', type: 'string', example: 'Ahmed Mohamed'),
                    new OA\Property(property: 'course_title', type: 'string', example: 'ECG Interpretation Masterclass'),
                    new OA\Property(property: 'instructor_name', type: 'string', nullable: true),
                    new OA\Property(property: 'issued_at', type: 'string', format: 'date-time'),
                    new OA\Property(property: 'expires_at', type: 'string', format: 'date-time'),
                    new OA\Property(property: 'download_url', type: 'string'),
                ])]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Certificate not found'),
        ]
    )]
    public function certificateDownload() {}

    // ==================== SUBSCRIPTIONS ====================

    #[OA\Get(
        path: '/student/subscriptions',
        summary: 'List student subscriptions',
        tags: ['Student - Subscriptions'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Subscriptions list', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object'))]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function subscriptionIndex() {}

    #[OA\Post(
        path: '/student/subscriptions',
        summary: 'Create subscription',
        description: 'Subscribes the student to a plan. End date is auto-calculated.',
        tags: ['Student - Subscriptions'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['plan_id'],
            properties: [
                new OA\Property(property: 'plan_id', type: 'integer', example: 3, description: 'Subscription plan ID'),
                new OA\Property(property: 'auto_renew', type: 'boolean', example: false),
            ]
        )),
        responses: [
            new OA\Response(response: 201, description: 'Subscription created', content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'data', type: 'object'),
                    new OA\Property(property: 'message', type: 'string', example: 'Subscription created successfully.'),
                ]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function subscriptionStore() {}

    #[OA\Put(
        path: '/student/subscriptions/{id}',
        summary: 'Update subscription',
        description: 'Updates subscription settings (auto_renew).',
        tags: ['Student - Subscriptions'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            properties: [new OA\Property(property: 'auto_renew', type: 'boolean', example: true)]
        )),
        responses: [
            new OA\Response(response: 200, description: 'Updated', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'object'), new OA\Property(property: 'message', type: 'string')]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function subscriptionUpdate() {}

    #[OA\Post(
        path: '/student/subscriptions/{id}/cancel',
        summary: 'Cancel subscription',
        description: 'Cancels an active subscription. Sets status to cancelled and disables auto-renew.',
        tags: ['Student - Subscriptions'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Cancelled', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'object'), new OA\Property(property: 'message', type: 'string')]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function subscriptionCancel() {}

    // ==================== ORDERS ====================

    #[OA\Get(
        path: '/student/orders',
        summary: 'List purchase history',
        tags: ['Student - Orders'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 10))],
        responses: [
            new OA\Response(response: 200, description: 'Orders list', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object')), new OA\Property(property: 'meta', type: 'object')]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function orderIndex() {}

    #[OA\Get(
        path: '/student/orders/{orderNumber}',
        summary: 'Get order details',
        tags: ['Student - Orders'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'orderNumber', in: 'path', required: true, description: 'Order number (e.g. ORD-AB12CD34)', schema: new OA\Schema(type: 'string'))],
        responses: [
            new OA\Response(response: 200, description: 'Order details with items and billing', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'object')]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Order not found'),
        ]
    )]
    public function orderShow() {}

    #[OA\Get(
        path: '/student/orders/{orderNumber}/receipt',
        summary: 'Get order receipt',
        tags: ['Student - Orders'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'orderNumber', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [
            new OA\Response(response: 200, description: 'Formatted receipt', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'object')]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Order not found'),
        ]
    )]
    public function orderReceipt() {}

    // ==================== REVIEWS ====================

    #[OA\Get(
        path: '/student/reviews',
        summary: 'List my reviews',
        description: 'Returns all reviews submitted by the authenticated student.',
        tags: ['Student - Reviews'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Reviews list', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object'))]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function reviewIndex() {}

    #[OA\Post(
        path: '/student/reviews',
        summary: 'Submit or update review',
        description: 'Creates a new review or updates existing. One review per course. Must be enrolled.',
        tags: ['Student - Reviews'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['course_id', 'rating'],
            properties: [
                new OA\Property(property: 'course_id', type: 'integer', example: 1),
                new OA\Property(property: 'rating', type: 'integer', minimum: 1, maximum: 5, example: 5),
                new OA\Property(property: 'comment', type: 'string', nullable: true, maxLength: 1000, example: 'Excellent clinical cases!'),
            ]
        )),
        responses: [
            new OA\Response(response: 201, description: 'Review submitted', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'object'), new OA\Property(property: 'message', type: 'string')]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Not enrolled in course'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function reviewStore() {}

    #[OA\Delete(
        path: '/student/reviews/{id}',
        summary: 'Delete review',
        tags: ['Student - Reviews'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Deleted', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'message', type: 'string', example: 'Review deleted successfully.')]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Review not found'),
        ]
    )]
    public function reviewDestroy() {}
}
