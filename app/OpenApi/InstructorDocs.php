<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

class InstructorDocs
{
    // ==================== DASHBOARD ====================

    #[OA\Get(
        path: '/instructor/dashboard',
        summary: 'Get instructor dashboard',
        description: 'Returns revenue, student count, rating, course count, revenue growth, and recent reviews.',
        tags: ['Instructor - Dashboard'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Dashboard stats', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'object', properties: [
                    new OA\Property(property: 'total_revenue', type: 'number', example: 12500.00),
                    new OA\Property(property: 'new_students_this_month', type: 'integer', example: 23),
                    new OA\Property(property: 'average_rating', type: 'number', example: 4.65),
                    new OA\Property(property: 'active_courses', type: 'integer', example: 5),
                    new OA\Property(property: 'revenue_growth', type: 'number', example: 15.5, description: 'Percentage change vs previous month'),
                    new OA\Property(property: 'recent_reviews', type: 'array', items: new OA\Items(type: 'object')),
                ])]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function dashboard() {}

    // ==================== COURSES CRUD ====================

    #[OA\Get(
        path: '/instructor/courses',
        summary: 'List my courses',
        description: 'Returns all courses owned by the instructor (including unpublished).',
        tags: ['Instructor - Courses'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 10))],
        responses: [
            new OA\Response(response: 200, description: 'Courses list', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object')), new OA\Property(property: 'meta', type: 'object')]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function courseIndex() {}

    #[OA\Post(
        path: '/instructor/courses',
        summary: 'Create a course',
        tags: ['Instructor - Courses'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['title', 'category_id', 'price'],
            properties: [
                new OA\Property(property: 'title', type: 'string', maxLength: 255, example: 'Advanced Clinical Cases'),
                new OA\Property(property: 'category_id', type: 'integer', example: 1),
                new OA\Property(property: 'short_description', type: 'string', maxLength: 500, nullable: true),
                new OA\Property(property: 'description', type: 'string', nullable: true),
                new OA\Property(property: 'image', type: 'string', maxLength: 500, nullable: true),
                new OA\Property(property: 'price', type: 'number', minimum: 0, example: 2000),
                new OA\Property(property: 'original_price', type: 'number', nullable: true, example: 2500),
                new OA\Property(property: 'level', type: 'string', enum: ['beginner', 'intermediate', 'advanced'], example: 'intermediate'),
                new OA\Property(property: 'language', type: 'string', maxLength: 100, example: 'Arabic & English'),
                new OA\Property(property: 'is_bundle', type: 'boolean', example: false),
                new OA\Property(property: 'requirements', type: 'array', items: new OA\Items(type: 'string'), example: ['Basic anatomy knowledge']),
                new OA\Property(property: 'learning_outcomes', type: 'array', items: new OA\Items(type: 'string'), example: ['Master ECG interpretation']),
                new OA\Property(property: 'tags', type: 'array', items: new OA\Items(type: 'string'), example: ['ecg', 'cardiology']),
            ]
        )),
        responses: [
            new OA\Response(response: 201, description: 'Course created', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'object'), new OA\Property(property: 'message', type: 'string')]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function courseStore() {}

    #[OA\Get(
        path: '/instructor/courses/{id}',
        summary: 'Get course detail',
        description: 'Returns full course detail with modules, lessons, reviews. Only own courses.',
        tags: ['Instructor - Courses'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Course detail', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'object')])),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Not your course'),
        ]
    )]
    public function courseShow() {}

    #[OA\Put(
        path: '/instructor/courses/{id}',
        summary: 'Update course',
        description: 'Updates course. All fields optional. Only the course owner can update.',
        tags: ['Instructor - Courses'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'title', type: 'string'),
                new OA\Property(property: 'category_id', type: 'integer'),
                new OA\Property(property: 'short_description', type: 'string'),
                new OA\Property(property: 'description', type: 'string'),
                new OA\Property(property: 'price', type: 'number'),
                new OA\Property(property: 'original_price', type: 'number'),
                new OA\Property(property: 'level', type: 'string', enum: ['beginner', 'intermediate', 'advanced']),
                new OA\Property(property: 'is_bundle', type: 'boolean'),
                new OA\Property(property: 'requirements', type: 'array', items: new OA\Items(type: 'string')),
                new OA\Property(property: 'learning_outcomes', type: 'array', items: new OA\Items(type: 'string')),
                new OA\Property(property: 'tags', type: 'array', items: new OA\Items(type: 'string')),
            ]
        )),
        responses: [
            new OA\Response(response: 200, description: 'Updated', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'object'), new OA\Property(property: 'message', type: 'string')])),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Not your course'),
        ]
    )]
    public function courseUpdate() {}

    #[OA\Delete(
        path: '/instructor/courses/{id}',
        summary: 'Delete course',
        tags: ['Instructor - Courses'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Deleted', content: new OA\JsonContent(properties: [new OA\Property(property: 'message', type: 'string')])),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Not your course'),
        ]
    )]
    public function courseDestroy() {}

    // ==================== MODULES ====================

    #[OA\Post(
        path: '/instructor/courses/{id}/modules',
        summary: 'Create module',
        tags: ['Instructor - Modules'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Course ID', schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['title'],
            properties: [
                new OA\Property(property: 'title', type: 'string', example: 'Module 1: Introduction'),
                new OA\Property(property: 'sort_order', type: 'integer', example: 1),
            ]
        )),
        responses: [
            new OA\Response(response: 201, description: 'Module created', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'object'), new OA\Property(property: 'message', type: 'string')])),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Not your course'),
        ]
    )]
    public function moduleStore() {}

    #[OA\Put(
        path: '/instructor/modules/{id}',
        summary: 'Update module',
        tags: ['Instructor - Modules'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Module ID', schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['title'],
            properties: [
                new OA\Property(property: 'title', type: 'string', example: 'Updated Module Title'),
                new OA\Property(property: 'sort_order', type: 'integer', example: 2),
            ]
        )),
        responses: [
            new OA\Response(response: 200, description: 'Updated', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'object'), new OA\Property(property: 'message', type: 'string')])),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function moduleUpdate() {}

    #[OA\Delete(
        path: '/instructor/modules/{id}',
        summary: 'Delete module',
        tags: ['Instructor - Modules'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Deleted', content: new OA\JsonContent(properties: [new OA\Property(property: 'message', type: 'string')])),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function moduleDestroy() {}

    // ==================== LESSONS ====================

    #[OA\Post(
        path: '/instructor/modules/{id}/lessons',
        summary: 'Create lesson',
        tags: ['Instructor - Lessons'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Module ID', schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['title'],
            properties: [
                new OA\Property(property: 'title', type: 'string', example: 'Introduction to ECG'),
                new OA\Property(property: 'type', type: 'string', enum: ['video', 'quiz', 'assignment', 'reading'], example: 'video'),
                new OA\Property(property: 'duration_minutes', type: 'integer', example: 25),
                new OA\Property(property: 'video_url', type: 'string', nullable: true, example: 'https://youtube.com/watch?v=...'),
                new OA\Property(property: 'content', type: 'string', nullable: true),
                new OA\Property(property: 'is_free', type: 'boolean', example: false, description: 'Free preview lesson'),
                new OA\Property(property: 'sort_order', type: 'integer', example: 1),
            ]
        )),
        responses: [
            new OA\Response(response: 201, description: 'Lesson created', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'object'), new OA\Property(property: 'message', type: 'string')])),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function lessonStore() {}

    #[OA\Put(
        path: '/instructor/lessons/{id}',
        summary: 'Update lesson',
        tags: ['Instructor - Lessons'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Lesson ID', schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'title', type: 'string'),
                new OA\Property(property: 'type', type: 'string', enum: ['video', 'quiz', 'assignment', 'reading']),
                new OA\Property(property: 'duration_minutes', type: 'integer'),
                new OA\Property(property: 'video_url', type: 'string', nullable: true),
                new OA\Property(property: 'content', type: 'string', nullable: true),
                new OA\Property(property: 'is_free', type: 'boolean'),
                new OA\Property(property: 'sort_order', type: 'integer'),
            ]
        )),
        responses: [
            new OA\Response(response: 200, description: 'Updated', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'object'), new OA\Property(property: 'message', type: 'string')])),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function lessonUpdate() {}

    #[OA\Delete(
        path: '/instructor/lessons/{id}',
        summary: 'Delete lesson',
        tags: ['Instructor - Lessons'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Deleted', content: new OA\JsonContent(properties: [new OA\Property(property: 'message', type: 'string')])),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function lessonDestroy() {}

    // ==================== QUIZZES ====================

    #[OA\Get(
        path: '/instructor/quizzes',
        summary: 'List instructor quizzes',
        tags: ['Instructor - Quizzes'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 15))],
        responses: [
            new OA\Response(response: 200, description: 'Quizzes list', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object')), new OA\Property(property: 'meta', type: 'object')])),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function quizIndex() {}

    #[OA\Post(
        path: '/instructor/quizzes',
        summary: 'Create quiz with questions',
        description: 'Creates a new quiz, optionally with inline questions and options.',
        tags: ['Instructor - Quizzes'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['course_id', 'title'],
            properties: [
                new OA\Property(property: 'course_id', type: 'integer', example: 1),
                new OA\Property(property: 'lesson_id', type: 'integer', nullable: true, example: 5),
                new OA\Property(property: 'title', type: 'string', example: 'Module 1 Quiz'),
                new OA\Property(property: 'passing_score', type: 'integer', minimum: 1, maximum: 100, example: 70),
                new OA\Property(property: 'time_limit_minutes', type: 'integer', example: 15),
                new OA\Property(property: 'max_attempts', type: 'integer', example: 3),
                new OA\Property(property: 'questions', type: 'array', items: new OA\Items(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'question_text', type: 'string', example: 'What is the first-line treatment?'),
                        new OA\Property(property: 'explanation', type: 'string', nullable: true),
                        new OA\Property(property: 'options', type: 'array', items: new OA\Items(
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'label', type: 'string', enum: ['A', 'B', 'C', 'D']),
                                new OA\Property(property: 'text', type: 'string'),
                                new OA\Property(property: 'is_correct', type: 'boolean'),
                            ]
                        )),
                    ]
                )),
            ]
        )),
        responses: [
            new OA\Response(response: 201, description: 'Quiz created', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'object'), new OA\Property(property: 'message', type: 'string')])),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Not your course'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function quizStore() {}

    #[OA\Get(
        path: '/instructor/quizzes/{id}',
        summary: 'Get quiz with questions',
        description: 'Returns quiz details with all questions and options (including is_correct).',
        tags: ['Instructor - Quizzes'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Quiz detail', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'object')])),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Not your course'),
        ]
    )]
    public function quizShow() {}

    #[OA\Put(
        path: '/instructor/quizzes/{id}',
        summary: 'Update quiz settings',
        tags: ['Instructor - Quizzes'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'title', type: 'string'),
                new OA\Property(property: 'lesson_id', type: 'integer', nullable: true),
                new OA\Property(property: 'passing_score', type: 'integer'),
                new OA\Property(property: 'time_limit_minutes', type: 'integer'),
                new OA\Property(property: 'max_attempts', type: 'integer'),
            ]
        )),
        responses: [
            new OA\Response(response: 200, description: 'Updated', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'object'), new OA\Property(property: 'message', type: 'string')])),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function quizUpdate() {}

    #[OA\Delete(
        path: '/instructor/quizzes/{id}',
        summary: 'Delete quiz',
        tags: ['Instructor - Quizzes'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Deleted', content: new OA\JsonContent(properties: [new OA\Property(property: 'message', type: 'string')])),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function quizDestroy() {}

    #[OA\Post(
        path: '/instructor/quizzes/{id}/questions',
        summary: 'Add question to quiz',
        description: 'Adds a question with exactly 4 options (A, B, C, D) and 1 correct answer.',
        tags: ['Instructor - Quizzes'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Quiz ID', schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['question_text', 'options'],
            properties: [
                new OA\Property(property: 'question_text', type: 'string', example: 'Which biomarker is most specific?'),
                new OA\Property(property: 'explanation', type: 'string', nullable: true, example: 'Troponin I is cardiac-specific...'),
                new OA\Property(property: 'sort_order', type: 'integer', example: 1),
                new OA\Property(property: 'options', type: 'array', minItems: 4, maxItems: 4, items: new OA\Items(
                    type: 'object',
                    required: ['label', 'text', 'is_correct'],
                    properties: [
                        new OA\Property(property: 'label', type: 'string', enum: ['A', 'B', 'C', 'D']),
                        new OA\Property(property: 'text', type: 'string'),
                        new OA\Property(property: 'is_correct', type: 'boolean'),
                    ]
                )),
            ]
        )),
        responses: [
            new OA\Response(response: 201, description: 'Question added', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'object'), new OA\Property(property: 'message', type: 'string')])),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Must have exactly one correct answer'),
        ]
    )]
    public function questionStore() {}

    #[OA\Put(
        path: '/instructor/questions/{id}',
        summary: 'Update question',
        tags: ['Instructor - Quizzes'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Question ID', schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'question_text', type: 'string'),
                new OA\Property(property: 'explanation', type: 'string', nullable: true),
                new OA\Property(property: 'sort_order', type: 'integer'),
                new OA\Property(property: 'options', type: 'array', description: 'Replaces all options', items: new OA\Items(type: 'object')),
            ]
        )),
        responses: [
            new OA\Response(response: 200, description: 'Updated', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'object'), new OA\Property(property: 'message', type: 'string')])),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function questionUpdate() {}

    #[OA\Delete(
        path: '/instructor/questions/{id}',
        summary: 'Delete question',
        tags: ['Instructor - Quizzes'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Deleted', content: new OA\JsonContent(properties: [new OA\Property(property: 'message', type: 'string')])),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function questionDestroy() {}

    // ==================== REVENUE ====================

    #[OA\Get(
        path: '/instructor/revenue',
        summary: 'Revenue overview',
        description: 'Returns available balance, pending clearance, and lifetime earnings.',
        tags: ['Instructor - Revenue'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Revenue summary', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'object', properties: [
                    new OA\Property(property: 'available_balance', type: 'number', example: 3500.00),
                    new OA\Property(property: 'pending_clearance', type: 'number', example: 800.00),
                    new OA\Property(property: 'lifetime_earnings', type: 'number', example: 12500.00),
                ])]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function revenueIndex() {}

    #[OA\Get(
        path: '/instructor/transactions',
        summary: 'List transactions',
        tags: ['Instructor - Revenue'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
            new OA\Parameter(name: 'filter', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['all', 'sales', 'payouts'], default: 'all')),
            new OA\Parameter(name: 'period', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['current_month', 'last_month', 'year_to_date'])),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Transactions list', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object')), new OA\Property(property: 'meta', type: 'object')]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function transactionIndex() {}

    #[OA\Post(
        path: '/instructor/payout-request',
        summary: 'Request payout',
        description: 'Submits a payout request. Amount must not exceed available balance.',
        tags: ['Instructor - Revenue'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['amount', 'payout_method'],
            properties: [
                new OA\Property(property: 'amount', type: 'number', minimum: 1, example: 500.00),
                new OA\Property(property: 'payout_method', type: 'string', maxLength: 100, example: 'bank_transfer'),
            ]
        )),
        responses: [
            new OA\Response(response: 201, description: 'Payout request submitted', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'object'), new OA\Property(property: 'message', type: 'string')]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Insufficient balance'),
        ]
    )]
    public function payoutRequest() {}
}
