<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

class FeaturePlanDocs
{
    // ==================== PUBLIC - STATS ====================

    #[OA\Get(
        path: '/stats',
        summary: 'Platform statistics',
        description: 'Returns cached (1 hour) platform-wide statistics: active students, total courses, total instructors, average rating.',
        tags: ['Public - Settings'],
        responses: [
            new OA\Response(response: 200, description: 'Platform stats', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'object', properties: [
                    new OA\Property(property: 'active_students', type: 'integer', example: 1250),
                    new OA\Property(property: 'total_courses', type: 'integer', example: 85),
                    new OA\Property(property: 'total_instructors', type: 'integer', example: 24),
                    new OA\Property(property: 'average_rating', type: 'number', format: 'float', example: 4.7),
                ])]
            )),
        ]
    )]
    public function stats() {}

    // ==================== PUBLIC - TESTIMONIALS ====================

    #[OA\Get(
        path: '/testimonials',
        summary: 'Top testimonials',
        description: 'Returns top approved reviews (rating >= 4) with user name, avatar, title and course title.',
        tags: ['Public - Courses'],
        parameters: [
            new OA\Parameter(name: 'limit', in: 'query', required: false, description: 'Number of testimonials to return (default 8)', schema: new OA\Schema(type: 'integer', default: 8)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'List of testimonials', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object', properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 1),
                    new OA\Property(property: 'rating', type: 'integer', example: 5),
                    new OA\Property(property: 'comment', type: 'string', example: 'Excellent course!'),
                    new OA\Property(property: 'user', type: 'object', properties: [
                        new OA\Property(property: 'name', type: 'string', example: 'Sara Ahmed'),
                        new OA\Property(property: 'avatar', type: 'string', example: '/storage/avatars/sara.jpg'),
                        new OA\Property(property: 'title', type: 'string', example: 'Medical Student'),
                    ]),
                    new OA\Property(property: 'course', type: 'object', properties: [
                        new OA\Property(property: 'title', type: 'string', example: 'Clinical Anatomy'),
                    ]),
                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                ]))]
            )),
        ]
    )]
    public function testimonials() {}

    // ==================== PUBLIC - INSTRUCTORS LIST ====================

    #[OA\Get(
        path: '/instructors',
        summary: 'List all active instructors',
        description: 'Returns all active instructors with specialization, courses count, total students, and average rating.',
        tags: ['Public - Instructors'],
        responses: [
            new OA\Response(response: 200, description: 'List of instructors', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object', properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 1),
                    new OA\Property(property: 'name', type: 'string', example: 'Dr. Ahmed Mohamed'),
                    new OA\Property(property: 'avatar', type: 'string', example: '/storage/avatars/ahmed.jpg'),
                    new OA\Property(property: 'specialization', type: 'string', example: 'Cardiology'),
                    new OA\Property(property: 'courses_count', type: 'integer', example: 12),
                    new OA\Property(property: 'total_students', type: 'integer', example: 450),
                    new OA\Property(property: 'average_rating', type: 'number', format: 'float', example: 4.8),
                ]))]
            )),
        ]
    )]
    public function instructorsList() {}

    // ==================== PUBLIC - CATEGORY DETAIL ====================

    #[OA\Get(
        path: '/categories/{slug}',
        summary: 'Category detail',
        description: 'Returns category details with courses count, total students, featured videos count, and courses list.',
        tags: ['Public - Categories'],
        parameters: [
            new OA\Parameter(name: 'slug', in: 'path', required: true, description: 'Category slug', schema: new OA\Schema(type: 'string', example: 'surgery')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Category detail', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'object', properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 1),
                    new OA\Property(property: 'name', type: 'string', example: 'Surgery'),
                    new OA\Property(property: 'slug', type: 'string', example: 'surgery'),
                    new OA\Property(property: 'description', type: 'string', example: 'Surgical courses and case studies.'),
                    new OA\Property(property: 'image', type: 'string', example: '/storage/categories/surgery.jpg'),
                    new OA\Property(property: 'courses_count', type: 'integer', example: 15),
                    new OA\Property(property: 'total_students', type: 'integer', example: 320),
                    new OA\Property(property: 'featured_videos_count', type: 'integer', example: 8),
                    new OA\Property(property: 'courses', type: 'array', items: new OA\Items(type: 'object')),
                ])]
            )),
            new OA\Response(response: 404, description: 'Category not found'),
        ]
    )]
    public function categoryDetail() {}

    // ==================== PUBLIC - CATEGORY VIDEOS ====================

    #[OA\Get(
        path: '/categories/{slug}/videos',
        summary: 'Free video lessons in category',
        description: 'Returns paginated free video lessons within a category with search and sorting.',
        tags: ['Public - Categories'],
        parameters: [
            new OA\Parameter(name: 'slug', in: 'path', required: true, description: 'Category slug', schema: new OA\Schema(type: 'string', example: 'surgery')),
            new OA\Parameter(name: 'search', in: 'query', required: false, description: 'Search by lesson title', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'sort_by', in: 'query', required: false, description: 'Sort order', schema: new OA\Schema(type: 'string', enum: ['most_viewed', 'newest', 'duration'], default: 'most_viewed')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, description: 'Items per page (default 8)', schema: new OA\Schema(type: 'integer', default: 8)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Paginated video lessons', content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object', properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 10),
                        new OA\Property(property: 'title', type: 'string', example: 'Introduction to Suturing'),
                        new OA\Property(property: 'video_url', type: 'string', example: 'https://...'),
                        new OA\Property(property: 'duration_minutes', type: 'integer', example: 15),
                        new OA\Property(property: 'views_count', type: 'integer', example: 1200),
                        new OA\Property(property: 'thumbnail', type: 'string', example: '/storage/thumbnails/suturing.jpg'),
                        new OA\Property(property: 'course', type: 'object', properties: [
                            new OA\Property(property: 'title', type: 'string', example: 'General Surgery Basics'),
                        ]),
                    ])),
                    new OA\Property(property: 'meta', type: 'object'),
                ]
            )),
            new OA\Response(response: 404, description: 'Category not found'),
        ]
    )]
    public function categoryVideos() {}

    // ==================== PUBLIC - PAGES (FAQ, TERMS, PRIVACY, ABOUT) ====================

    #[OA\Get(
        path: '/pages/{slug}',
        summary: 'Get page content (FAQ, Terms, Privacy, About)',
        description: 'Returns page content by slug. For "faq" returns questions grouped by category. For "terms" and "privacy" returns HTML content. For "about" returns structured about page data.',
        tags: ['Public - Settings'],
        parameters: [
            new OA\Parameter(name: 'slug', in: 'path', required: true, description: 'Page slug: faq, terms, privacy, or about', schema: new OA\Schema(type: 'string', enum: ['faq', 'terms', 'privacy', 'about'], example: 'faq')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Page content', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'object', properties: [
                    new OA\Property(property: 'slug', type: 'string', example: 'faq'),
                    new OA\Property(property: 'title', type: 'string', example: 'Frequently Asked Questions'),
                    new OA\Property(property: 'categories', type: 'object', description: 'Only for faq slug - questions grouped by category', example: '{"Subscriptions": [{"id": 1, "question": "...", "answer": "..."}]}'),
                    new OA\Property(property: 'content', type: 'string', description: 'Only for terms/privacy slugs - HTML content', example: '<h2>1. Acceptance of Terms</h2><p>...</p>'),
                    new OA\Property(property: 'last_updated', type: 'string', description: 'For terms/privacy/about slugs', example: 'October 2023'),
                    new OA\Property(property: 'description', type: 'string', description: 'Only for about slug', example: 'Empowering the next generation...'),
                    new OA\Property(property: 'mission', type: 'string', description: 'Only for about slug', example: 'To bridge the gap...'),
                    new OA\Property(property: 'vision', type: 'string', description: 'Only for about slug', example: 'To become the leading...'),
                    new OA\Property(property: 'values', type: 'array', description: 'Only for about slug', items: new OA\Items(type: 'object', properties: [
                        new OA\Property(property: 'title', type: 'string', example: 'Evidence-Based'),
                        new OA\Property(property: 'description', type: 'string', example: 'All our courses are rooted in...'),
                        new OA\Property(property: 'icon', type: 'string', example: 'book'),
                    ])),
                ])]
            )),
            new OA\Response(response: 404, description: 'Page not found'),
        ]
    )]
    public function legalPage() {}

    // ==================== AUTHENTICATED - CATEGORY SUBSCRIBE ====================

    #[OA\Post(
        path: '/categories/{slug}/subscribe',
        summary: 'Subscribe to category notifications',
        description: 'Subscribe to notifications for new courses in a category.',
        tags: ['Public - Categories'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'slug', in: 'path', required: true, description: 'Category slug', schema: new OA\Schema(type: 'string', example: 'surgery')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Subscribed', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'message', type: 'string', example: 'Subscribed to category notifications.')]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Category not found'),
            new OA\Response(response: 409, description: 'Already subscribed'),
        ]
    )]
    public function categorySubscribe() {}

    // ==================== AUTHENTICATED - CATEGORY UNSUBSCRIBE ====================

    #[OA\Delete(
        path: '/categories/{slug}/unsubscribe',
        summary: 'Unsubscribe from category notifications',
        description: 'Unsubscribe from notifications for a category.',
        tags: ['Public - Categories'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'slug', in: 'path', required: true, description: 'Category slug', schema: new OA\Schema(type: 'string', example: 'surgery')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Unsubscribed', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'message', type: 'string', example: 'Unsubscribed from category notifications.')]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Subscription not found'),
        ]
    )]
    public function categoryUnsubscribe() {}

    // ==================== STUDENT - SUBSCRIPTION INVOICE ====================

    #[OA\Get(
        path: '/student/subscriptions/{subscription}/invoice',
        summary: 'Download subscription invoice PDF',
        description: 'Downloads the subscription invoice as a PDF file.',
        tags: ['Student - Subscriptions'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'subscription', in: 'path', required: true, description: 'Subscription ID', schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'PDF file download', content: new OA\MediaType(mediaType: 'application/pdf')),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Subscription does not belong to you'),
            new OA\Response(response: 404, description: 'Subscription not found'),
        ]
    )]
    public function subscriptionInvoice() {}

    // ==================== INSTRUCTOR - EXPORT TRANSACTIONS ====================

    #[OA\Get(
        path: '/instructor/transactions/export',
        summary: 'Export transactions as CSV',
        description: 'Downloads all instructor transactions as a CSV file.',
        tags: ['Instructor - Revenue'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'CSV file download', content: new OA\MediaType(mediaType: 'text/csv')),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function transactionsExport() {}

    // ==================== INSTRUCTOR - COURSE STUDENTS ====================

    #[OA\Get(
        path: '/instructor/courses/{id}/students',
        summary: 'List enrolled students for a course',
        description: 'Returns paginated list of students enrolled in the instructor\'s course with their progress.',
        tags: ['Instructor - Courses'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Course ID', schema: new OA\Schema(type: 'integer', example: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, description: 'Items per page (default 15)', schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Paginated enrolled students', content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object', properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'student', type: 'object', properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 5),
                            new OA\Property(property: 'name', type: 'string', example: 'Sara Ahmed'),
                            new OA\Property(property: 'email', type: 'string', example: 'sara@example.com'),
                            new OA\Property(property: 'avatar', type: 'string', example: '/storage/avatars/sara.jpg'),
                        ]),
                        new OA\Property(property: 'progress_percentage', type: 'number', format: 'float', example: 72.5),
                        new OA\Property(property: 'enrolled_at', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'completed_at', type: 'string', format: 'date-time', nullable: true),
                        new OA\Property(property: 'last_activity_at', type: 'string', format: 'date-time'),
                    ])),
                    new OA\Property(property: 'meta', type: 'object'),
                ]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Course does not belong to you'),
            new OA\Response(response: 404, description: 'Course not found'),
        ]
    )]
    public function courseStudents() {}
}
