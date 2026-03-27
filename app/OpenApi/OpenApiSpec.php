<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'SPC Online Academy API',
    description: 'REST API for SPC Online Academy — a medical education platform. All endpoints are prefixed with `/api/v1`.',
    contact: new OA\Contact(
        name: 'SPC Academy Support',
        email: 'support@spc-academy.com'
    )
)]
#[OA\Server(
    url: '/api/v1',
    description: 'API Server'
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Token',
    description: 'Enter your Sanctum token (e.g., 1|abc123...)'
)]
#[OA\Tag(name: 'Auth', description: 'Authentication endpoints')]
#[OA\Tag(name: 'Public - Courses', description: 'Public course browsing')]
#[OA\Tag(name: 'Public - Categories', description: 'Course categories')]
#[OA\Tag(name: 'Public - Instructors', description: 'Instructor profiles')]
#[OA\Tag(name: 'Public - Subscriptions', description: 'Subscription plans')]
#[OA\Tag(name: 'Public - Contact', description: 'Contact messages')]
#[OA\Tag(name: 'Student - Dashboard', description: 'Student dashboard & stats')]
#[OA\Tag(name: 'Student - Courses', description: 'Student enrolled courses')]
#[OA\Tag(name: 'Student - Progress', description: 'Lesson progress tracking')]
#[OA\Tag(name: 'Student - Certificates', description: 'Student certificates')]
#[OA\Tag(name: 'Student - Subscriptions', description: 'Student subscription management')]
#[OA\Tag(name: 'Student - Orders', description: 'Student purchase history')]
#[OA\Tag(name: 'Student - Reviews', description: 'Student course reviews')]
#[OA\Tag(name: 'Student - Quizzes', description: 'Quiz taking')]
#[OA\Tag(name: 'Cart & Checkout', description: 'Shopping cart and checkout')]
#[OA\Tag(name: 'Instructor - Dashboard', description: 'Instructor dashboard & stats')]
#[OA\Tag(name: 'Instructor - Courses', description: 'Instructor course management')]
#[OA\Tag(name: 'Instructor - Modules', description: 'Module management')]
#[OA\Tag(name: 'Instructor - Lessons', description: 'Lesson management')]
#[OA\Tag(name: 'Instructor - Quizzes', description: 'Quiz management')]
#[OA\Tag(name: 'Instructor - Revenue', description: 'Revenue & payouts')]
#[OA\Tag(name: 'User Profile', description: 'Profile management')]
#[OA\Tag(name: 'Public - Bundles', description: 'Bundle details')]
#[OA\Tag(name: 'Student - Wishlist', description: 'Course wishlist management')]
#[OA\Tag(name: 'Student - Video Progress', description: 'Video playback progress tracking')]
#[OA\Tag(name: 'Student - Notes', description: 'Lesson note-taking')]
#[OA\Tag(name: 'Student - Notifications', description: 'Notification management')]
#[OA\Tag(name: 'Student - Devices', description: 'Device/session management')]
#[OA\Tag(name: 'Student - Refunds', description: 'Refund requests')]
#[OA\Tag(name: 'Instructor - Analytics', description: 'Course analytics')]
#[OA\Tag(name: 'Instructor - Resources', description: 'Lesson resource management')]
#[OA\Tag(name: 'Instructor - Bundles', description: 'Bundle course management')]
#[OA\Tag(name: 'Payments', description: 'Payment callback and verification')]
#[OA\Tag(name: 'Webhooks', description: 'Payment webhooks')]
#[OA\Tag(name: 'Public - Settings', description: 'Site settings')]
#[OA\Tag(name: 'User Preferences', description: 'Theme & language preferences')]
#[OA\Tag(name: 'Messages', description: 'Student-instructor messaging')]
#[OA\Tag(name: 'Student - Downloads', description: 'Offline downloads')]
#[OA\Tag(name: 'Course Shares', description: 'Course sharing tracking')]
#[OA\Tag(name: 'Student - Installments', description: 'Installment payment plans')]
#[OA\Tag(name: 'Social Auth', description: 'Social login via Google/Facebook OAuth')]
#[OA\Tag(name: 'Feature Flags', description: 'Feature flag management')]
#[OA\Tag(name: 'Account Management', description: 'Account deletion and GDPR')]
#[OA\Tag(name: 'Certificate PDF', description: 'Certificate PDF download and preview')]
class OpenApiSpec
{
}
