<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

class Phase3Docs
{
    // ==========================================
    // USER PREFERENCES
    // ==========================================

    #[OA\Get(
        path: '/user/preferences',
        summary: 'Get user preferences',
        tags: ['User Preferences'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'User preferences', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', type: 'object', properties: [
                    new OA\Property(property: 'theme', type: 'string', enum: ['light', 'dark'], example: 'light'),
                    new OA\Property(property: 'language', type: 'string', enum: ['en', 'ar'], example: 'ar'),
                    new OA\Property(property: 'direction', type: 'string', enum: ['ltr', 'rtl'], example: 'rtl'),
                ]),
            ])),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function preferencesShow() {}

    #[OA\Put(
        path: '/user/preferences',
        summary: 'Update user preferences',
        tags: ['User Preferences'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(properties: [
            new OA\Property(property: 'theme', type: 'string', enum: ['light', 'dark']),
            new OA\Property(property: 'language', type: 'string', enum: ['en', 'ar']),
        ])),
        responses: [
            new OA\Response(response: 200, description: 'Preferences updated'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function preferencesUpdate() {}

    // ==========================================
    // MESSAGES
    // ==========================================

    #[OA\Get(
        path: '/messages',
        summary: 'List messages (inbox or sent)',
        tags: ['Messages'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'filter', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['inbox', 'sent'], default: 'inbox'))],
        responses: [
            new OA\Response(response: 200, description: 'Messages list', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object')), new OA\Property(property: 'meta', type: 'object')])),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function messagesIndex() {}

    #[OA\Get(
        path: '/messages/unread-count',
        summary: 'Get unread message count',
        tags: ['Messages'],
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'Unread count', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'object', properties: [new OA\Property(property: 'count', type: 'integer', example: 3)])]))]
    )]
    public function messagesUnreadCount() {}

    #[OA\Get(
        path: '/messages/conversations',
        summary: 'List conversations grouped by user',
        tags: ['Messages'],
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'Conversations list', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object'))]))]
    )]
    public function messagesConversations() {}

    #[OA\Get(
        path: '/messages/conversation/{user}',
        summary: 'Get conversation thread with a user',
        tags: ['Messages'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Conversation thread', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object')), new OA\Property(property: 'meta', type: 'object')]))]
    )]
    public function messagesConversation() {}

    #[OA\Post(
        path: '/messages',
        summary: 'Send a message',
        description: 'Students can message instructors of enrolled courses. Instructors can message their students.',
        tags: ['Messages'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['receiver_id', 'body'], properties: [
            new OA\Property(property: 'receiver_id', type: 'integer', example: 2),
            new OA\Property(property: 'course_id', type: 'integer', nullable: true, example: 1),
            new OA\Property(property: 'subject', type: 'string', nullable: true, example: 'Question about Module 3'),
            new OA\Property(property: 'body', type: 'string', example: 'Dr., I have a question about...'),
        ])),
        responses: [
            new OA\Response(response: 201, description: 'Message sent'),
            new OA\Response(response: 403, description: 'Cannot message this user'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function messagesStore() {}

    #[OA\Put(
        path: '/messages/{message}/read',
        summary: 'Mark message as read',
        tags: ['Messages'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'message', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Marked as read')]
    )]
    public function messagesMarkAsRead() {}

    // ==========================================
    // OFFLINE DOWNLOADS
    // ==========================================

    #[OA\Post(
        path: '/student/courses/{course}/download-token',
        summary: 'Generate download token',
        description: 'Generates a 24-hour download token. Max 5 active downloads.',
        tags: ['Student - Downloads'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'course', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(properties: [
            new OA\Property(property: 'lesson_id', type: 'integer', nullable: true),
        ])),
        responses: [
            new OA\Response(response: 201, description: 'Download token generated', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'object', properties: [new OA\Property(property: 'download_url', type: 'string'), new OA\Property(property: 'expires_at', type: 'string', format: 'date-time')])])),
            new OA\Response(response: 403, description: 'Not enrolled'),
            new OA\Response(response: 422, description: 'Max downloads reached'),
        ]
    )]
    public function downloadToken() {}

    #[OA\Get(
        path: '/student/downloads',
        summary: 'List offline downloads',
        tags: ['Student - Downloads'],
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'Downloads list', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object')), new OA\Property(property: 'meta', type: 'object')]))]
    )]
    public function downloadsIndex() {}

    #[OA\Delete(
        path: '/student/downloads/{download}',
        summary: 'Remove download',
        tags: ['Student - Downloads'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'download', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Download removed')]
    )]
    public function downloadsDestroy() {}

    #[OA\Get(
        path: '/downloads/{token}',
        summary: 'Download by token (no auth)',
        tags: ['Student - Downloads'],
        parameters: [new OA\Parameter(name: 'token', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [
            new OA\Response(response: 200, description: 'Stream started'),
            new OA\Response(response: 410, description: 'Download link expired'),
        ]
    )]
    public function downloadByToken() {}

    // ==========================================
    // COURSE SHARES
    // ==========================================

    #[OA\Post(
        path: '/courses/{course}/share',
        summary: 'Track course share',
        tags: ['Course Shares'],
        parameters: [new OA\Parameter(name: 'course', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['platform'], properties: [
            new OA\Property(property: 'platform', type: 'string', enum: ['facebook', 'twitter', 'linkedin', 'whatsapp', 'copy_link']),
        ])),
        responses: [new OA\Response(response: 201, description: 'Share tracked')]
    )]
    public function courseShare() {}

    #[OA\Get(
        path: '/instructor/courses/{course}/shares',
        summary: 'Course share analytics',
        tags: ['Course Shares'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'course', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'period', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 30)),
        ],
        responses: [new OA\Response(response: 200, description: 'Share analytics', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'object', properties: [new OA\Property(property: 'total_shares', type: 'integer'), new OA\Property(property: 'by_platform', type: 'array', items: new OA\Items(type: 'object')), new OA\Property(property: 'period_days', type: 'integer')])]))]
    )]
    public function courseShareAnalytics() {}

    // ==========================================
    // INSTALLMENTS
    // ==========================================

    #[OA\Get(
        path: '/student/installments',
        summary: 'List installment plans',
        tags: ['Student - Installments'],
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'Installments list', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object')), new OA\Property(property: 'meta', type: 'object')]))]
    )]
    public function installmentsIndex() {}

    #[OA\Get(
        path: '/student/installments/{installment}',
        summary: 'Get installment plan details',
        tags: ['Student - Installments'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'installment', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Installment details', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'object')])),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function installmentsShow() {}
}
