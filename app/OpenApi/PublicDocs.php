<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

class PublicDocs
{
    // ==================== COURSES ====================

    #[OA\Get(
        path: '/courses',
        summary: 'List published courses',
        description: 'Returns paginated published courses with filtering, searching, and sorting.',
        tags: ['Public - Courses'],
        parameters: [
            new OA\Parameter(name: 'per_page', in: 'query', required: false, description: 'Items per page (default 9)', schema: new OA\Schema(type: 'integer', default: 9)),
            new OA\Parameter(name: 'category', in: 'query', required: false, description: 'Filter by category slug', schema: new OA\Schema(type: 'string', example: 'cardiology')),
            new OA\Parameter(name: 'search', in: 'query', required: false, description: 'Search in title and description', schema: new OA\Schema(type: 'string', example: 'ecg')),
            new OA\Parameter(name: 'level', in: 'query', required: false, description: 'Filter by level', schema: new OA\Schema(type: 'string', enum: ['beginner', 'intermediate', 'advanced'])),
            new OA\Parameter(name: 'min_price', in: 'query', required: false, description: 'Minimum price (EGP)', schema: new OA\Schema(type: 'number', example: 0)),
            new OA\Parameter(name: 'max_price', in: 'query', required: false, description: 'Maximum price (EGP)', schema: new OA\Schema(type: 'number', example: 5000)),
            new OA\Parameter(name: 'rating', in: 'query', required: false, description: 'Minimum average rating', schema: new OA\Schema(type: 'number', example: 4)),
            new OA\Parameter(name: 'is_bundle', in: 'query', required: false, description: 'Only bundle courses', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'sort_by', in: 'query', required: false, description: 'Sort order', schema: new OA\Schema(type: 'string', enum: ['newest', 'popular', 'highest_rated', 'price_low', 'price_high'], default: 'newest')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Courses list with pagination', content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object')),
                    new OA\Property(property: 'links', type: 'object'),
                    new OA\Property(property: 'meta', type: 'object', properties: [
                        new OA\Property(property: 'current_page', type: 'integer', example: 1),
                        new OA\Property(property: 'per_page', type: 'integer', example: 9),
                        new OA\Property(property: 'total', type: 'integer', example: 14),
                        new OA\Property(property: 'last_page', type: 'integer', example: 2),
                    ]),
                ]
            )),
        ]
    )]
    public function courseIndex() {}

    #[OA\Get(
        path: '/courses/featured',
        summary: 'List featured courses',
        description: 'Returns up to 8 featured published courses, ordered by newest first.',
        tags: ['Public - Courses'],
        responses: [
            new OA\Response(response: 200, description: 'Featured courses list', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object'))]
            )),
        ]
    )]
    public function courseFeatured() {}

    #[OA\Get(
        path: '/courses/{slug}',
        summary: 'Get course details',
        description: 'Returns full course detail by slug, including modules, lessons, reviews count, and instructor info.',
        tags: ['Public - Courses'],
        parameters: [
            new OA\Parameter(name: 'slug', in: 'path', required: true, description: 'Course slug', schema: new OA\Schema(type: 'string', example: 'ecg-interpretation-masterclass')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Course detail with modules and lessons', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'object')]
            )),
            new OA\Response(response: 404, description: 'Course not found'),
        ]
    )]
    public function courseShow() {}

    // ==================== CATEGORIES ====================

    #[OA\Get(
        path: '/categories',
        summary: 'List all categories',
        description: 'Returns all categories with their course count, ordered by sort_order.',
        tags: ['Public - Categories'],
        responses: [
            new OA\Response(response: 200, description: 'Categories list', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Cardiology'),
                        new OA\Property(property: 'slug', type: 'string', example: 'cardiology'),
                        new OA\Property(property: 'icon', type: 'string', example: 'heart'),
                        new OA\Property(property: 'description', type: 'string', nullable: true),
                        new OA\Property(property: 'courses_count', type: 'integer', example: 3),
                    ]
                ))]
            )),
        ]
    )]
    public function categoryIndex() {}

    // ==================== INSTRUCTORS ====================

    #[OA\Get(
        path: '/instructors/{id}',
        summary: 'Get instructor profile',
        description: 'Returns public instructor profile with their published courses.',
        tags: ['Public - Instructors'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Instructor user ID', schema: new OA\Schema(type: 'integer', example: 2)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Instructor profile with courses', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'object')]
            )),
            new OA\Response(response: 404, description: 'Instructor not found'),
        ]
    )]
    public function instructorShow() {}

    // ==================== SUBSCRIPTION PLANS ====================

    #[OA\Get(
        path: '/subscription-plans',
        summary: 'List subscription plans',
        description: 'Returns all active subscription plans ordered by duration_months.',
        tags: ['Public - Subscriptions'],
        responses: [
            new OA\Response(response: 200, description: 'Subscription plans', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Monthly'),
                        new OA\Property(property: 'duration_months', type: 'integer', example: 1),
                        new OA\Property(property: 'price_per_month', type: 'number', example: 450.00),
                        new OA\Property(property: 'total_price', type: 'number', example: 450.00),
                        new OA\Property(property: 'savings_percentage', type: 'number', example: 0),
                        new OA\Property(property: 'features', type: 'array', items: new OA\Items(type: 'string')),
                        new OA\Property(property: 'is_popular', type: 'boolean', example: false),
                    ]
                ))]
            )),
        ]
    )]
    public function subscriptionPlanIndex() {}

    // ==================== CONTACT ====================

    #[OA\Post(
        path: '/contact',
        summary: 'Send contact message',
        description: 'Submits a contact message from the website contact form.',
        tags: ['Public - Contact'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['name', 'email', 'message'],
            properties: [
                new OA\Property(property: 'name', type: 'string', maxLength: 255, example: 'Ahmed Mohamed'),
                new OA\Property(property: 'email', type: 'string', format: 'email', maxLength: 255, example: 'ahmed@example.com'),
                new OA\Property(property: 'subject', type: 'string', maxLength: 255, nullable: true, example: 'Enrollment question'),
                new OA\Property(property: 'message', type: 'string', example: 'I would like to know about the cardiology course...'),
            ]
        )),
        responses: [
            new OA\Response(response: 201, description: 'Message sent successfully', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'message', type: 'string', example: 'Your message has been sent successfully. We will get back to you soon.')]
            )),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function contactStore() {}

    #[OA\Get(
        path: '/settings',
        summary: 'Get site settings',
        description: 'Returns all public site settings. Sensitive settings (payment keys, platform fee) are excluded.',
        tags: ['Public - Settings'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Site settings',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'site_name', type: 'string', example: 'SPC Online Academy'),
                            new OA\Property(property: 'logo', type: 'string', example: '/images/logo-spc.png'),
                            new OA\Property(property: 'site_description', type: 'string', example: 'Empowering Medical Professionals'),
                            new OA\Property(property: 'contact_phone', type: 'string', example: '+20 100 123 4567'),
                            new OA\Property(property: 'contact_email', type: 'string', example: 'support@spc-academy.com'),
                            new OA\Property(property: 'address', type: 'string', example: 'Cairo, Egypt'),
                            new OA\Property(property: 'working_hours', type: 'string', example: 'Sun - Thu: 9:00 AM - 5:00 PM'),
                            new OA\Property(property: 'primary_color', type: 'string', example: '#236bba'),
                            new OA\Property(property: 'secondary_color', type: 'string', example: '#0f172a'),
                            new OA\Property(property: 'facebook_url', type: 'string', nullable: true),
                            new OA\Property(property: 'twitter_url', type: 'string', nullable: true),
                            new OA\Property(property: 'instagram_url', type: 'string', nullable: true),
                            new OA\Property(property: 'linkedin_url', type: 'string', nullable: true),
                            new OA\Property(property: 'youtube_url', type: 'string', nullable: true),
                            new OA\Property(property: 'meta_title', type: 'string', nullable: true),
                            new OA\Property(property: 'meta_description', type: 'string', nullable: true),
                            new OA\Property(property: 'meta_keywords', type: 'string', nullable: true),
                            new OA\Property(property: 'currency', type: 'string', example: 'EGP'),
                            new OA\Property(property: 'maintenance_mode', type: 'boolean', example: false),
                            new OA\Property(property: 'maintenance_message', type: 'string', nullable: true),
                        ]),
                    ]
                )
            ),
        ]
    )]
    public function settings() {}
}
