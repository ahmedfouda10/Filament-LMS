<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

class Phase2Docs
{
    // ==================== PUBLIC - BUNDLES ====================

    #[OA\Get(
        path: '/bundles/{slug}',
        summary: 'Get bundle details',
        description: 'Returns bundle course details with included courses.',
        tags: ['Public - Bundles'],
        parameters: [
            new OA\Parameter(name: 'slug', in: 'path', required: true, description: 'Bundle slug', schema: new OA\Schema(type: 'string', example: 'full-stack-development-bundle')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Bundle details with included courses', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'object', properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 1),
                    new OA\Property(property: 'title', type: 'string', example: 'Full-Stack Development Bundle'),
                    new OA\Property(property: 'slug', type: 'string', example: 'full-stack-development-bundle'),
                    new OA\Property(property: 'price', type: 'number', example: 199.99),
                    new OA\Property(property: 'is_bundle', type: 'boolean', example: true),
                    new OA\Property(property: 'courses', type: 'array', items: new OA\Items(type: 'object')),
                ])]
            )),
            new OA\Response(response: 404, description: 'Bundle not found'),
        ]
    )]
    public function bundleShow() {}

    // ==================== STUDENT - WISHLIST ====================

    #[OA\Get(
        path: '/student/wishlist',
        summary: 'List wishlisted courses',
        description: 'Returns a paginated list of courses the student has wishlisted.',
        tags: ['Student - Wishlist'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'per_page', in: 'query', required: false, description: 'Items per page (default 10)', schema: new OA\Schema(type: 'integer', default: 10)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Paginated wishlist', content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object')),
                    new OA\Property(property: 'meta', type: 'object'),
                ]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function wishlistIndex() {}

    #[OA\Post(
        path: '/student/wishlist/{course}',
        summary: 'Add course to wishlist',
        description: 'Adds a course to the student\'s wishlist.',
        tags: ['Student - Wishlist'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'course', in: 'path', required: true, description: 'Course ID', schema: new OA\Schema(type: 'integer', example: 3)),
        ],
        responses: [
            new OA\Response(response: 201, description: 'Course added to wishlist', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'message', type: 'string', example: 'Course added to wishlist.')]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Already in wishlist'),
        ]
    )]
    public function wishlistStore() {}

    #[OA\Delete(
        path: '/student/wishlist/{course}',
        summary: 'Remove course from wishlist',
        description: 'Removes a course from the student\'s wishlist.',
        tags: ['Student - Wishlist'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'course', in: 'path', required: true, description: 'Course ID', schema: new OA\Schema(type: 'integer', example: 3)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Course removed from wishlist', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'message', type: 'string', example: 'Course removed from wishlist.')]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Not in wishlist'),
        ]
    )]
    public function wishlistDestroy() {}

    // ==================== STUDENT - VIDEO PROGRESS ====================

    #[OA\Put(
        path: '/student/lessons/{lesson}/video-progress',
        summary: 'Update video progress',
        description: 'Saves the student\'s current video playback position for a lesson.',
        tags: ['Student - Video Progress'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'lesson', in: 'path', required: true, description: 'Lesson ID', schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['progress_seconds', 'duration_seconds'],
                properties: [
                    new OA\Property(property: 'progress_seconds', type: 'integer', example: 120, description: 'Current playback position in seconds'),
                    new OA\Property(property: 'duration_seconds', type: 'integer', example: 600, description: 'Total video duration in seconds'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Video progress updated', content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'data', type: 'object', properties: [
                        new OA\Property(property: 'lesson_id', type: 'integer', example: 1),
                        new OA\Property(property: 'progress_seconds', type: 'integer', example: 120),
                        new OA\Property(property: 'duration_seconds', type: 'integer', example: 600),
                        new OA\Property(property: 'progress_percentage', type: 'number', example: 20.0),
                    ]),
                    new OA\Property(property: 'message', type: 'string', example: 'Video progress updated.'),
                ]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function videoProgressUpdate() {}

    #[OA\Get(
        path: '/student/lessons/{lesson}/video-progress',
        summary: 'Get video progress',
        description: 'Returns the student\'s current video playback position for a lesson.',
        tags: ['Student - Video Progress'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'lesson', in: 'path', required: true, description: 'Lesson ID', schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Video progress data', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'object', properties: [
                    new OA\Property(property: 'lesson_id', type: 'integer', example: 1),
                    new OA\Property(property: 'progress_seconds', type: 'integer', example: 120),
                    new OA\Property(property: 'duration_seconds', type: 'integer', example: 600),
                    new OA\Property(property: 'progress_percentage', type: 'number', example: 20.0),
                ])]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function videoProgressShow() {}

    // ==================== STUDENT - NOTES ====================

    #[OA\Get(
        path: '/student/lessons/{lesson}/notes',
        summary: 'List lesson notes',
        description: 'Returns all notes the student has created for a specific lesson.',
        tags: ['Student - Notes'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'lesson', in: 'path', required: true, description: 'Lesson ID', schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'List of notes', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'content', type: 'string', example: 'Important: remember drug interactions'),
                        new OA\Property(property: 'timestamp_seconds', type: 'integer', example: 95, nullable: true),
                        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                    ]
                ))]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function noteIndex() {}

    #[OA\Post(
        path: '/student/lessons/{lesson}/notes',
        summary: 'Create a note',
        description: 'Creates a new note for a specific lesson.',
        tags: ['Student - Notes'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'lesson', in: 'path', required: true, description: 'Lesson ID', schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['content'],
                properties: [
                    new OA\Property(property: 'content', type: 'string', example: 'Key formula for calculating dosage', description: 'Note content (max 5000)'),
                    new OA\Property(property: 'timestamp_seconds', type: 'integer', example: 180, description: 'Video timestamp in seconds (min 0)'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Note created', content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'data', type: 'object'),
                    new OA\Property(property: 'message', type: 'string', example: 'Note created successfully.'),
                ]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function noteStore() {}

    #[OA\Put(
        path: '/student/notes/{note}',
        summary: 'Update a note',
        description: 'Updates an existing note owned by the student.',
        tags: ['Student - Notes'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'note', in: 'path', required: true, description: 'Note ID', schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['content'],
                properties: [
                    new OA\Property(property: 'content', type: 'string', example: 'Updated note content', description: 'Note content (max 5000)'),
                    new OA\Property(property: 'timestamp_seconds', type: 'integer', example: 180, description: 'Video timestamp in seconds (min 0)'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Note updated', content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'data', type: 'object'),
                    new OA\Property(property: 'message', type: 'string', example: 'Note updated successfully.'),
                ]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Not the note owner'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function noteUpdate() {}

    #[OA\Delete(
        path: '/student/notes/{note}',
        summary: 'Delete a note',
        description: 'Deletes a note owned by the student.',
        tags: ['Student - Notes'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'note', in: 'path', required: true, description: 'Note ID', schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Note deleted', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'message', type: 'string', example: 'Note deleted successfully.')]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Not the note owner'),
        ]
    )]
    public function noteDestroy() {}

    // ==================== STUDENT - NOTIFICATIONS ====================

    #[OA\Get(
        path: '/student/notifications',
        summary: 'List notifications',
        description: 'Returns a paginated list of the student\'s notifications.',
        tags: ['Student - Notifications'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'per_page', in: 'query', required: false, description: 'Items per page (default 15)', schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Paginated notifications', content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'id', type: 'string', example: 'a1b2c3d4-e5f6-7890-abcd-ef1234567890'),
                            new OA\Property(property: 'type', type: 'string', example: 'course_completed'),
                            new OA\Property(property: 'title', type: 'string', example: 'Course Completed'),
                            new OA\Property(property: 'message', type: 'string', example: 'Congratulations! You have completed React Fundamentals.'),
                            new OA\Property(property: 'data', type: 'object'),
                            new OA\Property(property: 'read_at', type: 'string', format: 'date-time', nullable: true),
                            new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                        ]
                    )),
                    new OA\Property(property: 'meta', type: 'object'),
                ]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function notificationIndex() {}

    #[OA\Get(
        path: '/student/notifications/unread-count',
        summary: 'Get unread notification count',
        description: 'Returns the number of unread notifications.',
        tags: ['Student - Notifications'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Unread count', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'object', properties: [
                    new OA\Property(property: 'unread_count', type: 'integer', example: 5),
                ])]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function notificationUnreadCount() {}

    #[OA\Put(
        path: '/student/notifications/{notification}/read',
        summary: 'Mark notification as read',
        description: 'Marks a single notification as read.',
        tags: ['Student - Notifications'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'notification', in: 'path', required: true, description: 'Notification ID (UUID)', schema: new OA\Schema(type: 'string', example: 'a1b2c3d4-e5f6-7890-abcd-ef1234567890')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Notification marked as read', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'message', type: 'string', example: 'Notification marked as read.')]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Notification not found'),
        ]
    )]
    public function notificationMarkRead() {}

    #[OA\Put(
        path: '/student/notifications/read-all',
        summary: 'Mark all notifications as read',
        description: 'Marks all unread notifications as read.',
        tags: ['Student - Notifications'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'All notifications marked as read', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'message', type: 'string', example: 'All notifications marked as read.')]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function notificationMarkAllRead() {}

    // ==================== STUDENT - NOTIFICATION PREFERENCES ====================

    #[OA\Get(
        path: '/student/notification-preferences',
        summary: 'Get notification preferences',
        description: 'Returns the student\'s notification preference settings.',
        tags: ['Student - Notifications'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Notification preferences', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'object', properties: [
                    new OA\Property(property: 'email_notifications', type: 'boolean', example: true),
                    new OA\Property(property: 'push_notifications', type: 'boolean', example: true),
                    new OA\Property(property: 'course_updates', type: 'boolean', example: true),
                    new OA\Property(property: 'promotional', type: 'boolean', example: false),
                    new OA\Property(property: 'quiz_reminders', type: 'boolean', example: true),
                    new OA\Property(property: 'certificate_issued', type: 'boolean', example: true),
                ])]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function notificationPreferencesShow() {}

    #[OA\Put(
        path: '/student/notification-preferences',
        summary: 'Update notification preferences',
        description: 'Updates the student\'s notification preference settings.',
        tags: ['Student - Notifications'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'email_notifications', type: 'boolean', example: true),
                    new OA\Property(property: 'push_notifications', type: 'boolean', example: false),
                    new OA\Property(property: 'course_updates', type: 'boolean', example: true),
                    new OA\Property(property: 'promotional', type: 'boolean', example: false),
                    new OA\Property(property: 'quiz_reminders', type: 'boolean', example: true),
                    new OA\Property(property: 'certificate_issued', type: 'boolean', example: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Preferences updated', content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'data', type: 'object'),
                    new OA\Property(property: 'message', type: 'string', example: 'Notification preferences updated.'),
                ]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function notificationPreferencesUpdate() {}

    // ==================== STUDENT - DEVICES ====================

    #[OA\Get(
        path: '/student/devices',
        summary: 'List devices',
        description: 'Returns a list of devices (active sessions/tokens) for the student.',
        tags: ['Student - Devices'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'List of devices', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Chrome on Windows'),
                        new OA\Property(property: 'ip_address', type: 'string', example: '192.168.1.1'),
                        new OA\Property(property: 'last_active_at', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'is_current', type: 'boolean', example: true),
                    ]
                ))]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function deviceIndex() {}

    #[OA\Delete(
        path: '/student/devices/{device}',
        summary: 'Revoke a device',
        description: 'Revokes a specific device session (deletes the token). Cannot revoke the current device.',
        tags: ['Student - Devices'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'device', in: 'path', required: true, description: 'Device/Token ID', schema: new OA\Schema(type: 'integer', example: 2)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Device revoked', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'message', type: 'string', example: 'Device revoked successfully.')]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Cannot revoke current device'),
        ]
    )]
    public function deviceDestroy() {}

    // ==================== STUDENT - REFUNDS ====================

    #[OA\Post(
        path: '/student/orders/{order_number}/refund',
        summary: 'Request a refund',
        description: 'Submits a refund request for a completed order.',
        tags: ['Student - Refunds'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'order_number', in: 'path', required: true, description: 'Order number (e.g. ORD-AB12CD34)', schema: new OA\Schema(type: 'string', example: 'ORD-AB12CD34')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['reason'],
                properties: [
                    new OA\Property(property: 'reason', type: 'string', example: 'Course content did not match description.', description: 'Reason for refund (max 1000)'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Refund request submitted', content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'data', type: 'object', properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'order_number', type: 'string', example: 'ORD-AB12CD34'),
                        new OA\Property(property: 'status', type: 'string', example: 'refund_requested'),
                        new OA\Property(property: 'refund_reason', type: 'string', example: 'Course content did not match description.'),
                        new OA\Property(property: 'requested_at', type: 'string', format: 'date-time'),
                    ]),
                    new OA\Property(property: 'message', type: 'string', example: 'Refund request submitted successfully.'),
                ]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Refund already requested or validation error'),
        ]
    )]
    public function refundRequest() {}

    // ==================== INSTRUCTOR - ANALYTICS ====================

    #[OA\Get(
        path: '/instructor/courses/{course}/analytics',
        summary: 'Get course analytics',
        description: 'Returns detailed analytics data for a specific course owned by the instructor.',
        tags: ['Instructor - Analytics'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'course', in: 'path', required: true, description: 'Course ID', schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Course analytics data', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'object', properties: [
                    new OA\Property(property: 'course_id', type: 'integer', example: 1),
                    new OA\Property(property: 'total_enrollments', type: 'integer', example: 150),
                    new OA\Property(property: 'active_students', type: 'integer', example: 120),
                    new OA\Property(property: 'completion_rate', type: 'number', example: 65.5),
                    new OA\Property(property: 'average_rating', type: 'number', example: 4.5),
                    new OA\Property(property: 'total_revenue', type: 'number', example: 5250.00),
                    new OA\Property(property: 'average_progress', type: 'number', example: 72.3),
                    new OA\Property(property: 'enrollments_over_time', type: 'array', items: new OA\Items(type: 'object')),
                    new OA\Property(property: 'revenue_over_time', type: 'array', items: new OA\Items(type: 'object')),
                    new OA\Property(property: 'lesson_completion_rates', type: 'array', items: new OA\Items(type: 'object')),
                ])]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Not the course owner'),
        ]
    )]
    public function courseAnalytics() {}

    // ==================== INSTRUCTOR - RESOURCES ====================

    #[OA\Post(
        path: '/instructor/lessons/{lesson}/resources',
        summary: 'Upload lesson resource',
        description: 'Uploads a downloadable resource file for a lesson. Accepts pdf, doc, docx, ppt, pptx, xls, xlsx, zip. Max 10MB.',
        tags: ['Instructor - Resources'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'lesson', in: 'path', required: true, description: 'Lesson ID', schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['file', 'title'],
                    properties: [
                        new OA\Property(property: 'file', type: 'string', format: 'binary', description: 'Resource file (max 10MB)'),
                        new OA\Property(property: 'title', type: 'string', example: 'Lecture Slides - Week 1', description: 'Resource title (max 255)'),
                        new OA\Property(property: 'description', type: 'string', example: 'PDF slides covering pharmacokinetics basics', description: 'Resource description (max 500)'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Resource uploaded', content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'data', type: 'object', properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'title', type: 'string', example: 'Lecture Slides - Week 1'),
                        new OA\Property(property: 'description', type: 'string', example: 'PDF slides covering pharmacokinetics basics'),
                        new OA\Property(property: 'file_url', type: 'string', example: '/storage/resources/lecture-slides-week1.pdf'),
                        new OA\Property(property: 'file_size', type: 'integer', example: 2048576),
                        new OA\Property(property: 'file_type', type: 'string', example: 'pdf'),
                        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                    ]),
                    new OA\Property(property: 'message', type: 'string', example: 'Resource uploaded successfully.'),
                ]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function resourceStore() {}

    #[OA\Put(
        path: '/instructor/resources/{resource}',
        summary: 'Update resource',
        description: 'Updates resource metadata (title and description).',
        tags: ['Instructor - Resources'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'resource', in: 'path', required: true, description: 'Resource ID', schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'Updated Lecture Slides', description: 'Resource title (max 255)'),
                    new OA\Property(property: 'description', type: 'string', example: 'Updated description', description: 'Resource description (max 500)'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Resource updated', content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'data', type: 'object'),
                    new OA\Property(property: 'message', type: 'string', example: 'Resource updated successfully.'),
                ]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Not the resource owner'),
        ]
    )]
    public function resourceUpdate() {}

    #[OA\Delete(
        path: '/instructor/resources/{resource}',
        summary: 'Delete resource',
        description: 'Deletes a lesson resource and its file from storage.',
        tags: ['Instructor - Resources'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'resource', in: 'path', required: true, description: 'Resource ID', schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Resource deleted', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'message', type: 'string', example: 'Resource deleted successfully.')]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Not the resource owner'),
        ]
    )]
    public function resourceDestroy() {}

    // ==================== INSTRUCTOR - BUNDLES ====================

    #[OA\Post(
        path: '/instructor/courses/{course}/bundle-items',
        summary: 'Add course to bundle',
        description: 'Adds an existing course as an item within a bundle course. The target course must be a bundle (is_bundle = true).',
        tags: ['Instructor - Bundles'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'course', in: 'path', required: true, description: 'Bundle course ID', schema: new OA\Schema(type: 'integer', example: 5)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['course_id'],
                properties: [
                    new OA\Property(property: 'course_id', type: 'integer', example: 3, description: 'Course ID to add to the bundle'),
                    new OA\Property(property: 'sort_order', type: 'integer', example: 1, description: 'Display order within the bundle (min 0)'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Course added to bundle', content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'data', type: 'object', properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'bundle_course_id', type: 'integer', example: 5),
                        new OA\Property(property: 'course_id', type: 'integer', example: 3),
                        new OA\Property(property: 'sort_order', type: 'integer', example: 1),
                        new OA\Property(property: 'course', type: 'object'),
                    ]),
                    new OA\Property(property: 'message', type: 'string', example: 'Course added to bundle.'),
                ]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Not a bundle or validation error'),
        ]
    )]
    public function bundleItemStore() {}

    #[OA\Delete(
        path: '/instructor/bundle-items/{bundleItem}',
        summary: 'Remove course from bundle',
        description: 'Removes a course from a bundle.',
        tags: ['Instructor - Bundles'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'bundleItem', in: 'path', required: true, description: 'Bundle item ID', schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Course removed from bundle', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'message', type: 'string', example: 'Course removed from bundle.')]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Not the bundle owner'),
        ]
    )]
    public function bundleItemDestroy() {}

    #[OA\Put(
        path: '/instructor/courses/{course}/bundle-items/reorder',
        summary: 'Reorder bundle items',
        description: 'Reorders the courses within a bundle.',
        tags: ['Instructor - Bundles'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'course', in: 'path', required: true, description: 'Bundle course ID', schema: new OA\Schema(type: 'integer', example: 5)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['order'],
                properties: [
                    new OA\Property(property: 'order', type: 'array', items: new OA\Items(type: 'integer'), example: [3, 1, 2], description: 'Array of bundle item IDs in the new order'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Bundle items reordered', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'message', type: 'string', example: 'Bundle items reordered successfully.')]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Not the bundle owner'),
        ]
    )]
    public function bundleItemReorder() {}

    // ==================== WEBHOOKS ====================

    #[OA\Post(
        path: '/payments/webhook',
        summary: 'Paymob payment webhook',
        description: 'Receives payment status updates from Paymob gateway. Verified by HMAC signature. On success: updates order to completed and creates enrollments. On failure: updates order to failed.',
        tags: ['Webhooks'],
        requestBody: new OA\RequestBody(
            required: true,
            description: 'Paymob webhook payload',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'obj', type: 'object', description: 'Paymob transaction object'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Webhook processed', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'message', type: 'string', example: 'Webhook processed successfully.')]
            )),
            new OA\Response(response: 400, description: 'Invalid webhook signature'),
        ]
    )]
    public function paymobWebhook() {}
}
