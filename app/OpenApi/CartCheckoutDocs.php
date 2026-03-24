<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

class CartCheckoutDocs
{
    // ==================== CART ====================

    #[OA\Get(
        path: '/cart',
        summary: 'View cart',
        description: 'Returns cart items, applied promo code, and calculated totals.',
        tags: ['Cart & Checkout'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Cart data', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'object', properties: [
                    new OA\Property(property: 'id', type: 'integer'),
                    new OA\Property(property: 'items', type: 'array', items: new OA\Items(type: 'object')),
                    new OA\Property(property: 'promo_code', type: 'object', nullable: true),
                    new OA\Property(property: 'subtotal', type: 'number', example: 3500.00),
                    new OA\Property(property: 'discount', type: 'number', example: 875.00),
                    new OA\Property(property: 'total', type: 'number', example: 2625.00),
                ])]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function cartIndex() {}

    #[OA\Post(
        path: '/cart/items',
        summary: 'Add item to cart',
        description: 'Adds a published course to the cart. Validates course is not already in cart and student is not already enrolled.',
        tags: ['Cart & Checkout'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['course_id'],
            properties: [new OA\Property(property: 'course_id', type: 'integer', example: 3)]
        )),
        responses: [
            new OA\Response(response: 201, description: 'Course added to cart', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'object'), new OA\Property(property: 'message', type: 'string')]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Already enrolled or already in cart'),
        ]
    )]
    public function cartAddItem() {}

    #[OA\Delete(
        path: '/cart/items/{id}',
        summary: 'Remove item from cart',
        tags: ['Cart & Checkout'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Cart item ID', schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Item removed', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'object'), new OA\Property(property: 'message', type: 'string')]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function cartRemoveItem() {}

    #[OA\Delete(
        path: '/cart',
        summary: 'Clear cart',
        description: 'Removes all items and applied promo code.',
        tags: ['Cart & Checkout'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Cart cleared', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'message', type: 'string', example: 'Cart cleared successfully.')]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function cartClear() {}

    #[OA\Post(
        path: '/cart/promo',
        summary: 'Apply promo code',
        description: 'Validates and applies a promo code. Checks: active, not expired, not max used, min purchase met.',
        tags: ['Cart & Checkout'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['code'],
            properties: [new OA\Property(property: 'code', type: 'string', example: 'SPC25')]
        )),
        responses: [
            new OA\Response(response: 200, description: 'Promo applied', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'object'), new OA\Property(property: 'message', type: 'string')]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Invalid, expired, or max uses reached'),
        ]
    )]
    public function cartApplyPromo() {}

    #[OA\Delete(
        path: '/cart/promo',
        summary: 'Remove promo code',
        tags: ['Cart & Checkout'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Promo removed', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'object'), new OA\Property(property: 'message', type: 'string')]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function cartRemovePromo() {}

    // ==================== CHECKOUT ====================

    #[OA\Post(
        path: '/checkout',
        summary: 'Process checkout',
        description: 'Creates order, enrollments, and instructor transactions. Applies promo discount. Clears cart. All in a DB transaction.',
        tags: ['Cart & Checkout'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['payment_method'],
            properties: [
                new OA\Property(property: 'payment_method', type: 'string', enum: ['credit_card', 'mobile_wallet', 'bank_transfer', 'installment'], example: 'credit_card'),
                new OA\Property(property: 'billing_street', type: 'string', nullable: true, example: '123 Tahrir St'),
                new OA\Property(property: 'billing_city', type: 'string', nullable: true, example: 'Cairo'),
                new OA\Property(property: 'billing_state', type: 'string', nullable: true, example: 'Cairo'),
                new OA\Property(property: 'billing_country', type: 'string', nullable: true, example: 'Egypt'),
                new OA\Property(property: 'billing_postal_code', type: 'string', nullable: true, example: '11511'),
            ]
        )),
        responses: [
            new OA\Response(response: 201, description: 'Checkout completed', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'object', description: 'Order with items'), new OA\Property(property: 'message', type: 'string')]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Cart empty or already enrolled'),
        ]
    )]
    public function checkout() {}

    // ==================== STUDENT QUIZZES ====================

    #[OA\Get(
        path: '/quizzes/{id}',
        summary: 'Get quiz for taking',
        description: 'Returns quiz with questions and options. is_correct is NOT included (to prevent cheating). Student must be enrolled.',
        tags: ['Student - Quizzes'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Quiz ID', schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Quiz with questions (without correct answers)', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'object')]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Not enrolled in course'),
        ]
    )]
    public function quizShow() {}

    #[OA\Post(
        path: '/quizzes/{id}/attempt',
        summary: 'Submit quiz attempt',
        description: 'Grades answers, calculates score, determines pass/fail. If passed, marks lesson complete and may generate certificate.',
        tags: ['Student - Quizzes'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Quiz ID', schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['answers'],
            properties: [
                new OA\Property(
                    property: 'answers',
                    type: 'object',
                    description: 'Map of question_id to selected option label (A/B/C/D)',
                    example: ['1' => 'A', '2' => 'C', '3' => 'B']
                ),
            ]
        )),
        responses: [
            new OA\Response(response: 200, description: 'Quiz graded', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'object', properties: [
                    new OA\Property(property: 'score', type: 'number', example: 80.0),
                    new OA\Property(property: 'passed', type: 'boolean', example: true),
                    new OA\Property(property: 'attempt_number', type: 'integer', example: 1),
                    new OA\Property(property: 'total_questions', type: 'integer', example: 5),
                    new OA\Property(property: 'correct_count', type: 'integer', example: 4),
                    new OA\Property(property: 'answers', type: 'array', items: new OA\Items(
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'question_id', type: 'integer'),
                            new OA\Property(property: 'question_text', type: 'string'),
                            new OA\Property(property: 'selected_answer', type: 'string', example: 'A'),
                            new OA\Property(property: 'correct_answer', type: 'string', example: 'A'),
                            new OA\Property(property: 'is_correct', type: 'boolean'),
                            new OA\Property(property: 'explanation', type: 'string', nullable: true),
                        ]
                    )),
                ])]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Not enrolled'),
            new OA\Response(response: 422, description: 'Max attempts reached'),
        ]
    )]
    public function quizAttempt() {}

    // ==================== PROFILE ====================

    #[OA\Put(
        path: '/user/profile',
        summary: 'Update profile',
        description: 'Updates user profile. Instructors can also update bio, specialization, qualifications, education, expertise, and social links.',
        tags: ['User Profile'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'Dr. Ahmed Hassan'),
                new OA\Property(property: 'phone', type: 'string', example: '+201001234567'),
                new OA\Property(property: 'bio', type: 'string', nullable: true, description: 'Instructor only'),
                new OA\Property(property: 'specialization', type: 'string', nullable: true, description: 'Instructor only'),
                new OA\Property(property: 'years_of_experience', type: 'integer', description: 'Instructor only'),
                new OA\Property(property: 'qualifications', type: 'array', items: new OA\Items(type: 'string'), description: 'Instructor only', example: ['MBBS', 'MD', 'Fellowship']),
                new OA\Property(property: 'education', type: 'array', items: new OA\Items(type: 'object'), description: 'Instructor only'),
                new OA\Property(property: 'expertise', type: 'array', items: new OA\Items(type: 'string'), description: 'Instructor only'),
                new OA\Property(property: 'social_links', type: 'object', description: 'Instructor only', example: ['linkedin' => 'https://...', 'twitter' => 'https://...']),
            ]
        )),
        responses: [
            new OA\Response(response: 200, description: 'Profile updated', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'object'), new OA\Property(property: 'message', type: 'string')]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function profileUpdate() {}

    #[OA\Put(
        path: '/user/password',
        summary: 'Change password',
        tags: ['User Profile'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['current_password', 'password', 'password_confirmation'],
            properties: [
                new OA\Property(property: 'current_password', type: 'string', format: 'password'),
                new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 8),
                new OA\Property(property: 'password_confirmation', type: 'string'),
            ]
        )),
        responses: [
            new OA\Response(response: 200, description: 'Password changed', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'message', type: 'string', example: 'Password updated successfully.')]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Current password incorrect'),
        ]
    )]
    public function passwordUpdate() {}

    #[OA\Post(
        path: '/user/avatar',
        summary: 'Upload avatar',
        tags: ['User Profile'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(
                properties: [new OA\Property(property: 'avatar', type: 'string', format: 'binary', description: 'Image file (jpeg, png, jpg, gif, webp). Max 2MB.')]
            )
        )),
        responses: [
            new OA\Response(response: 200, description: 'Avatar uploaded', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'object', properties: [new OA\Property(property: 'avatar', type: 'string')]), new OA\Property(property: 'message', type: 'string')]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Invalid image or too large'),
        ]
    )]
    public function avatarUpload() {}

    #[OA\Delete(
        path: '/user/avatar',
        summary: 'Delete avatar',
        tags: ['User Profile'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Avatar removed', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'message', type: 'string', example: 'Avatar removed successfully.')]
            )),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function avatarDelete() {}
}
