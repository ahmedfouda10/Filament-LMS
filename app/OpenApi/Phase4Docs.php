<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

class Phase4Docs
{
    // ==========================================
    // SOCIAL AUTH
    // ==========================================

    #[OA\Get(
        path: '/auth/social/{provider}/redirect',
        summary: 'Redirect to OAuth provider',
        description: 'Redirects the user to the Google or Facebook OAuth authorization page.',
        tags: ['Social Auth'],
        parameters: [
            new OA\Parameter(name: 'provider', in: 'path', required: true, schema: new OA\Schema(type: 'string', enum: ['google', 'facebook']), description: 'OAuth provider name'),
        ],
        responses: [
            new OA\Response(response: 302, description: 'Redirect to OAuth provider consent screen'),
            new OA\Response(response: 422, description: 'Unsupported social provider'),
        ]
    )]
    public function socialRedirect() {}

    #[OA\Get(
        path: '/auth/social/{provider}/callback',
        summary: 'OAuth callback handler',
        description: 'Handles the OAuth callback after user authorization. Creates or links the user account and returns an auth token.',
        tags: ['Social Auth'],
        parameters: [
            new OA\Parameter(name: 'provider', in: 'path', required: true, schema: new OA\Schema(type: 'string', enum: ['google', 'facebook'])),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Login successful', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Login successful.'),
                new OA\Property(property: 'token', type: 'string', example: '1|abc123...'),
                new OA\Property(property: 'user', type: 'object', properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 1),
                    new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
                    new OA\Property(property: 'email', type: 'string', example: 'john@example.com'),
                    new OA\Property(property: 'role', type: 'string', example: 'student'),
                ]),
            ])),
            new OA\Response(response: 422, description: 'Unsupported social provider'),
        ]
    )]
    public function socialCallback() {}

    #[OA\Post(
        path: '/auth/social/{provider}/token',
        summary: 'Token-based social auth for SPA/mobile',
        description: 'Accepts an access token obtained directly from the OAuth provider (Google/Facebook) and returns an API auth token. Ideal for SPA and mobile clients.',
        tags: ['Social Auth'],
        parameters: [
            new OA\Parameter(name: 'provider', in: 'path', required: true, schema: new OA\Schema(type: 'string', enum: ['google', 'facebook'])),
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['access_token'], properties: [
            new OA\Property(property: 'access_token', type: 'string', example: 'ya29.a0AfH6SM...', description: 'Access token from the OAuth provider'),
        ])),
        responses: [
            new OA\Response(response: 200, description: 'Login successful', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Login successful.'),
                new OA\Property(property: 'token', type: 'string', example: '1|abc123...'),
                new OA\Property(property: 'user', type: 'object', properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 1),
                    new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
                    new OA\Property(property: 'email', type: 'string', example: 'john@example.com'),
                    new OA\Property(property: 'role', type: 'string', example: 'student'),
                ]),
            ])),
            new OA\Response(response: 401, description: 'Unable to authenticate with provider'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function socialToken() {}

    // ==========================================
    // FEATURE FLAGS
    // ==========================================

    #[OA\Get(
        path: '/features',
        summary: 'Get all feature flags',
        description: 'Returns a boolean map of all feature flags indicating which features are enabled or disabled.',
        tags: ['Feature Flags'],
        responses: [
            new OA\Response(response: 200, description: 'Feature flags', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', type: 'object', additionalProperties: new OA\AdditionalProperties(type: 'boolean'), example: [
                    'social_login' => true,
                    'installments' => false,
                    'offline_downloads' => true,
                    'messaging' => true,
                    'bundles' => false,
                ]),
            ])),
        ]
    )]
    public function featureFlags() {}

    // ==========================================
    // ACCOUNT MANAGEMENT
    // ==========================================

    #[OA\Delete(
        path: '/user/account',
        summary: 'Delete account with GDPR anonymization',
        description: 'Permanently deletes the authenticated user\'s account with GDPR-compliant data anonymization. Requires password confirmation.',
        tags: ['Account Management'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['password'], properties: [
            new OA\Property(property: 'password', type: 'string', example: 'current_password', description: 'Current password for confirmation'),
            new OA\Property(property: 'reason', type: 'string', nullable: true, example: 'No longer need the service', description: 'Optional reason for account deletion'),
        ])),
        responses: [
            new OA\Response(response: 200, description: 'Account deleted', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Account deleted successfully. Your data has been anonymized.'),
            ])),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Incorrect password'),
        ]
    )]
    public function deleteAccount() {}

    // ==========================================
    // CERTIFICATE PDF
    // ==========================================

    #[OA\Get(
        path: '/student/certificates/{certificate}/pdf',
        summary: 'Download certificate as PDF',
        description: 'Downloads the certificate as a PDF file. Returns binary PDF content with Content-Disposition: attachment.',
        tags: ['Certificate PDF'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'certificate', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), description: 'Certificate ID'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'PDF file download', content: new OA\MediaType(mediaType: 'application/pdf', schema: new OA\Schema(type: 'string', format: 'binary'))),
            new OA\Response(response: 403, description: 'Certificate does not belong to you'),
            new OA\Response(response: 404, description: 'Certificate not found'),
        ]
    )]
    public function certificatePdf() {}

    #[OA\Get(
        path: '/student/certificates/{certificate}/preview',
        summary: 'Preview certificate PDF in browser',
        description: 'Renders the certificate PDF inline in the browser. Returns binary PDF content with Content-Disposition: inline.',
        tags: ['Certificate PDF'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'certificate', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), description: 'Certificate ID'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Inline PDF preview', content: new OA\MediaType(mediaType: 'application/pdf', schema: new OA\Schema(type: 'string', format: 'binary'))),
            new OA\Response(response: 403, description: 'Certificate does not belong to you'),
            new OA\Response(response: 404, description: 'Certificate not found'),
        ]
    )]
    public function certificatePreview() {}
}
