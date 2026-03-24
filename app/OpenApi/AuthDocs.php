<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

class AuthDocs
{
    #[OA\Post(
        path: '/auth/register',
        summary: 'Register a new user',
        description: 'Creates a new student or instructor account and returns an auth token.',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['name', 'email', 'phone', 'password', 'password_confirmation'],
            properties: [
                new OA\Property(property: 'name', type: 'string', maxLength: 255, example: 'Ahmed Mohamed'),
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'ahmed@example.com'),
                new OA\Property(property: 'phone', type: 'string', maxLength: 20, example: '+201001234567'),
                new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 8, example: 'password123'),
                new OA\Property(property: 'password_confirmation', type: 'string', example: 'password123'),
                new OA\Property(property: 'role', type: 'string', enum: ['student', 'instructor'], example: 'student'),
            ]
        )),
        responses: [
            new OA\Response(response: 201, description: 'Registration successful', content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'data', type: 'object', description: 'User object'),
                    new OA\Property(property: 'token', type: 'string', example: '1|abc123xyz...'),
                    new OA\Property(property: 'message', type: 'string', example: 'Registration successful.'),
                ]
            )),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'message', type: 'string', example: 'The given data was invalid.'),
                    new OA\Property(property: 'errors', type: 'object'),
                ]
            )),
        ]
    )]
    public function register() {}

    #[OA\Post(
        path: '/auth/login',
        summary: 'Login user',
        description: 'Authenticates a user by email/phone and password. The role must match the user\'s actual role.',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['email', 'password', 'role'],
            properties: [
                new OA\Property(property: 'email', type: 'string', description: 'Email or phone number', example: 'ahmed@spc-academy.com'),
                new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 8, example: 'password'),
                new OA\Property(property: 'role', type: 'string', enum: ['student', 'instructor'], example: 'instructor'),
            ]
        )),
        responses: [
            new OA\Response(response: 200, description: 'Login successful', content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'data', type: 'object', description: 'User object'),
                    new OA\Property(property: 'token', type: 'string', example: '2|def456uvw...'),
                    new OA\Property(property: 'message', type: 'string', example: 'Login successful.'),
                ]
            )),
            new OA\Response(response: 422, description: 'Invalid credentials or role mismatch'),
        ]
    )]
    public function login() {}

    #[OA\Post(
        path: '/auth/logout',
        summary: 'Logout user',
        description: 'Revokes the current authentication token.',
        tags: ['Auth'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Logged out', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'message', type: 'string', example: 'Logged out successfully.')]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function logout() {}

    #[OA\Get(
        path: '/auth/user',
        summary: 'Get authenticated user',
        description: 'Returns the current user with instructor profile if applicable.',
        tags: ['Auth'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'User data', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'object', description: 'User object with profile')]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function user() {}

    #[OA\Post(
        path: '/auth/refresh',
        summary: 'Refresh auth token',
        description: 'Deletes the current token and issues a new one.',
        tags: ['Auth'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'New token issued', content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'data', type: 'object'),
                    new OA\Property(property: 'token', type: 'string', example: '3|ghi789rst...'),
                ]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function refresh() {}

    #[OA\Post(
        path: '/auth/forgot-password',
        summary: 'Send password reset link',
        description: 'Sends a password reset link to the provided email address.',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['email'],
            properties: [new OA\Property(property: 'email', type: 'string', format: 'email', example: 'ahmed@example.com')]
        )),
        responses: [
            new OA\Response(response: 200, description: 'Reset link sent', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'message', type: 'string', example: 'Password reset link sent.')]
            )),
            new OA\Response(response: 422, description: 'Email not found'),
        ]
    )]
    public function forgotPassword() {}

    #[OA\Post(
        path: '/auth/reset-password',
        summary: 'Reset password',
        description: 'Resets the password using a token received via email. All existing tokens are revoked.',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['token', 'email', 'password', 'password_confirmation'],
            properties: [
                new OA\Property(property: 'token', type: 'string', description: 'Reset token from email'),
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'ahmed@example.com'),
                new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 8),
                new OA\Property(property: 'password_confirmation', type: 'string'),
            ]
        )),
        responses: [
            new OA\Response(response: 200, description: 'Password reset successful', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'message', type: 'string', example: 'Password reset successful.')]
            )),
            new OA\Response(response: 422, description: 'Invalid token or validation error'),
        ]
    )]
    public function resetPassword() {}
}
