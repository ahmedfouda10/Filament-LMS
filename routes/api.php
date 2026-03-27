<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CertificateController;
use App\Http\Controllers\Api\V1\CheckoutController;
use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\CourseController;
use App\Http\Controllers\Api\V1\InstructorController;
use App\Http\Controllers\Api\V1\InstructorCourseController;
use App\Http\Controllers\Api\V1\InstructorDashboardController;
use App\Http\Controllers\Api\V1\InstructorRevenueController;
use App\Http\Controllers\Api\V1\LessonController;
use App\Http\Controllers\Api\V1\LessonProgressController;
use App\Http\Controllers\Api\V1\ModuleController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\InstructorQuizController;
use App\Http\Controllers\Api\V1\QuizController;
use App\Http\Controllers\Api\V1\StudentReviewController;
use App\Http\Controllers\Api\V1\StudentCourseController;
use App\Http\Controllers\Api\V1\StudentDashboardController;
use App\Http\Controllers\Api\V1\StudentOrderController;
use App\Http\Controllers\Api\V1\StudentSubscriptionController;
use App\Http\Controllers\Api\V1\AccountDeletionController;
use App\Http\Controllers\Api\V1\BundleController;
use App\Http\Controllers\Api\V1\CertificatePdfController;
use App\Http\Controllers\Api\V1\FeatureFlagController;
use App\Http\Controllers\Api\V1\PageController;
use App\Http\Controllers\Api\V1\SocialAuthController;
use App\Http\Controllers\Api\V1\CourseShareController;
use App\Http\Controllers\Api\V1\InstallmentController;
use App\Http\Controllers\Api\V1\MessageController;
use App\Http\Controllers\Api\V1\OfflineDownloadController;
use App\Http\Controllers\Api\V1\UserPreferenceController;
use App\Http\Controllers\Api\V1\CourseAnalyticsController;
use App\Http\Controllers\Api\V1\LessonNoteController;
use App\Http\Controllers\Api\V1\LessonResourceApiController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\NotificationPreferenceController;
use App\Http\Controllers\Api\V1\PaymentWebhookController;
use App\Http\Controllers\Api\V1\RefundRequestController;
use App\Http\Controllers\Api\V1\SettingsController;
use App\Http\Controllers\Api\V1\SubscriptionInvoiceController;
use App\Http\Controllers\Api\V1\StatsController;
use App\Http\Controllers\Api\V1\TestimonialController;
use App\Http\Controllers\Api\V1\SubscriptionPlanController;
use App\Http\Controllers\Api\V1\UserDeviceController;
use App\Http\Controllers\Api\V1\VideoProgressController;
use App\Http\Controllers\Api\V1\WishlistController;
use Illuminate\Support\Facades\Route;

// All routes are automatically prefixed with /api by Laravel

Route::prefix('v1')->group(function () {

    // ==========================================
    // AUTH ROUTES (public)
    // ==========================================
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);

        // Protected auth routes
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/user', [AuthController::class, 'user']);
            Route::post('/refresh', [AuthController::class, 'refresh']);
        });
    });

    // ==========================================
    // PUBLIC ROUTES
    // ==========================================
    Route::get('/courses', [CourseController::class, 'index']);
    Route::get('/courses/featured', [CourseController::class, 'featured']);
    Route::get('/courses/{slug}', [CourseController::class, 'show'])->middleware(\App\Http\Middleware\TrackCourseView::class);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{slug}', [CategoryController::class, 'show']);
    Route::get('/categories/{slug}/videos', [CategoryController::class, 'videos']);
    Route::get('/instructors', [InstructorController::class, 'index']);
    Route::get('/instructors/{id}', [InstructorController::class, 'show']);
    Route::get('/subscription-plans', [SubscriptionPlanController::class, 'index']);
    Route::post('/contact', [ContactController::class, 'store']);
    Route::get('/settings', [SettingsController::class, 'index']);
    Route::get('/stats', [StatsController::class, 'index']);
    Route::get('/testimonials', [TestimonialController::class, 'index']);
    Route::get('/bundles/{slug}', [BundleController::class, 'show']);
    Route::post('/courses/{course}/share', [CourseShareController::class, 'store']);
    Route::get('/features', [FeatureFlagController::class, 'index']);
    Route::get('/pages/{slug}', [PageController::class, 'show']);

    // Social Auth
    Route::get('/auth/social/{provider}/redirect', [SocialAuthController::class, 'redirect'])->whereIn('provider', ['google', 'facebook']);
    Route::get('/auth/social/{provider}/callback', [SocialAuthController::class, 'callback'])->whereIn('provider', ['google', 'facebook']);
    Route::post('/auth/social/{provider}/token', [SocialAuthController::class, 'handleToken'])->whereIn('provider', ['google', 'facebook']);
    Route::get('/downloads/{token}', [OfflineDownloadController::class, 'download']);

    // ==========================================
    // AUTHENTICATED ROUTES
    // ==========================================
    Route::middleware('auth:sanctum')->group(function () {

        // User Profile
        Route::put('/user/profile', [ProfileController::class, 'update']);

        // User Preferences
        Route::get('/user/preferences', [UserPreferenceController::class, 'show']);
        Route::put('/user/preferences', [UserPreferenceController::class, 'update']);

        // Account Deletion
        Route::delete('/user/account', [AccountDeletionController::class, 'destroy']);

        // Category Subscribe (#20)
        Route::post('/categories/{slug}/subscribe', [CategoryController::class, 'subscribe']);
        Route::delete('/categories/{slug}/unsubscribe', [CategoryController::class, 'unsubscribe']);

        // Messages (any authenticated user)
        Route::get('/messages', [MessageController::class, 'index']);
        Route::get('/messages/unread-count', [MessageController::class, 'unreadCount']);
        Route::get('/messages/conversations', [MessageController::class, 'conversations']);
        Route::get('/messages/conversation/{user}', [MessageController::class, 'conversation']);
        Route::post('/messages', [MessageController::class, 'store']);
        Route::put('/messages/{message}/read', [MessageController::class, 'markAsRead']);
        Route::put('/user/password', [ProfileController::class, 'updatePassword']);
        Route::post('/user/avatar', [ProfileController::class, 'uploadAvatar']);
        Route::delete('/user/avatar', [ProfileController::class, 'deleteAvatar']);

        // Cart & Checkout (any authenticated user)
        Route::get('/cart', [CartController::class, 'index']);
        Route::post('/cart/items', [CartController::class, 'addItem']);
        Route::delete('/cart/items/{id}', [CartController::class, 'removeItem']);
        Route::delete('/cart', [CartController::class, 'clear']);
        Route::post('/cart/promo', [CartController::class, 'applyPromo']);
        Route::delete('/cart/promo', [CartController::class, 'removePromo']);
        Route::post('/checkout', [CheckoutController::class, 'process']);

        // ==========================================
        // STUDENT ROUTES
        // ==========================================
        Route::middleware('role:student')->prefix('student')->group(function () {
            // Dashboard
            Route::get('/dashboard', [StudentDashboardController::class, 'index']);

            // My Courses
            Route::get('/courses', [StudentCourseController::class, 'index']);
            Route::get('/courses/{id}/player', [StudentCourseController::class, 'player']);

            // Progress
            Route::post('/lessons/{id}/complete', [LessonProgressController::class, 'complete']);
            Route::get('/courses/{id}/progress', [LessonProgressController::class, 'show']);

            // Certificates
            Route::get('/certificates', [CertificateController::class, 'index']);
            Route::get('/certificates/{id}/download', [CertificateController::class, 'download']);
            Route::get('/certificates/{certificate}/pdf', [CertificatePdfController::class, 'download']);
            Route::get('/certificates/{certificate}/preview', [CertificatePdfController::class, 'preview']);

            // Subscriptions
            Route::get('/subscriptions', [StudentSubscriptionController::class, 'index']);
            Route::post('/subscriptions', [StudentSubscriptionController::class, 'store']);
            Route::put('/subscriptions/{id}', [StudentSubscriptionController::class, 'update']);
            Route::post('/subscriptions/{id}/cancel', [StudentSubscriptionController::class, 'cancel']);

            // Reviews
            Route::get('/reviews', [StudentReviewController::class, 'index']);
            Route::post('/reviews', [StudentReviewController::class, 'store']);
            Route::delete('/reviews/{id}', [StudentReviewController::class, 'destroy']);

            // Orders
            Route::get('/orders', [StudentOrderController::class, 'index']);
            Route::get('/orders/{orderNumber}', [StudentOrderController::class, 'show']);
            Route::get('/orders/{orderNumber}/receipt', [StudentOrderController::class, 'receipt']);
            Route::post('/orders/{order:order_number}/refund', [RefundRequestController::class, 'store']);

            // Wishlist
            Route::get('/wishlist', [WishlistController::class, 'index']);
            Route::post('/wishlist/{course}', [WishlistController::class, 'store']);
            Route::delete('/wishlist/{course}', [WishlistController::class, 'destroy']);

            // Video Progress
            Route::put('/lessons/{lesson}/video-progress', [VideoProgressController::class, 'update']);
            Route::get('/lessons/{lesson}/video-progress', [VideoProgressController::class, 'show']);

            // Lesson Notes
            Route::get('/lessons/{lesson}/notes', [LessonNoteController::class, 'index']);
            Route::post('/lessons/{lesson}/notes', [LessonNoteController::class, 'store']);
            Route::put('/notes/{note}', [LessonNoteController::class, 'update']);
            Route::delete('/notes/{note}', [LessonNoteController::class, 'destroy']);

            // Notifications
            Route::get('/notifications', [NotificationController::class, 'index']);
            Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
            Route::put('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
            Route::put('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

            // Notification Preferences
            Route::get('/notification-preferences', [NotificationPreferenceController::class, 'show']);
            Route::put('/notification-preferences', [NotificationPreferenceController::class, 'update']);

            // Devices
            Route::get('/devices', [UserDeviceController::class, 'index']);
            Route::delete('/devices/{device}', [UserDeviceController::class, 'destroy']);

            // Offline Downloads
            Route::post('/courses/{course}/download-token', [OfflineDownloadController::class, 'generateToken']);
            Route::get('/downloads', [OfflineDownloadController::class, 'index']);
            Route::delete('/downloads/{download}', [OfflineDownloadController::class, 'destroy']);

            // Subscription Invoice (#29)
            Route::get('/subscriptions/{subscription}/invoice', [SubscriptionInvoiceController::class, 'download']);

            // Installments
            Route::get('/installments', [InstallmentController::class, 'index']);
            Route::get('/installments/{installment}', [InstallmentController::class, 'show']);
        });

        // Quiz routes (for students)
        Route::middleware('role:student')->group(function () {
            Route::get('/quizzes/{id}', [QuizController::class, 'show']);
            Route::post('/quizzes/{id}/attempt', [QuizController::class, 'attempt']);
        });

        // ==========================================
        // INSTRUCTOR ROUTES
        // ==========================================
        Route::middleware('role:instructor')->prefix('instructor')->group(function () {
            // Dashboard
            Route::get('/dashboard', [InstructorDashboardController::class, 'index']);

            // Course Management
            Route::get('/courses', [InstructorCourseController::class, 'index']);
            Route::post('/courses', [InstructorCourseController::class, 'store']);
            Route::get('/courses/{id}', [InstructorCourseController::class, 'show']);
            Route::put('/courses/{id}', [InstructorCourseController::class, 'update']);
            Route::delete('/courses/{id}', [InstructorCourseController::class, 'destroy']);

            // Module Management
            Route::post('/courses/{id}/modules', [ModuleController::class, 'store']);
            Route::put('/modules/{id}', [ModuleController::class, 'update']);
            Route::delete('/modules/{id}', [ModuleController::class, 'destroy']);
            Route::put('/courses/{id}/modules/reorder', [ModuleController::class, 'reorder']);

            // Lesson Management
            Route::post('/modules/{id}/lessons', [LessonController::class, 'store']);
            Route::put('/lessons/{id}', [LessonController::class, 'update']);
            Route::delete('/lessons/{id}', [LessonController::class, 'destroy']);
            Route::put('/modules/{id}/lessons/reorder', [LessonController::class, 'reorder']);

            // Quiz Management
            Route::get('/quizzes', [InstructorQuizController::class, 'index']);
            Route::post('/quizzes', [InstructorQuizController::class, 'store']);
            Route::get('/quizzes/{id}', [InstructorQuizController::class, 'show']);
            Route::put('/quizzes/{id}', [InstructorQuizController::class, 'update']);
            Route::delete('/quizzes/{id}', [InstructorQuizController::class, 'destroy']);
            Route::post('/quizzes/{id}/questions', [InstructorQuizController::class, 'storeQuestion']);
            Route::put('/questions/{id}', [InstructorQuizController::class, 'updateQuestion']);
            Route::delete('/questions/{id}', [InstructorQuizController::class, 'destroyQuestion']);

            // Revenue
            Route::get('/revenue', [InstructorRevenueController::class, 'index']);
            Route::get('/transactions', [InstructorRevenueController::class, 'transactions']);
            Route::post('/payout-request', [InstructorRevenueController::class, 'requestPayout']);
            Route::get('/transactions/export', [InstructorRevenueController::class, 'exportCsv']);

            // Enrolled Students (#35)
            Route::get('/courses/{id}/students', [InstructorCourseController::class, 'students']);

            // Course Analytics
            Route::get('/courses/{course}/analytics', [CourseAnalyticsController::class, 'show']);
            Route::get('/courses/{course}/shares', [CourseShareController::class, 'analytics']);

            // Lesson Resources
            Route::post('/lessons/{lesson}/resources', [LessonResourceApiController::class, 'store']);
            Route::put('/resources/{resource}', [LessonResourceApiController::class, 'update']);
            Route::delete('/resources/{resource}', [LessonResourceApiController::class, 'destroy']);

            // Bundle Management
            Route::post('/courses/{course}/bundle-items', [BundleController::class, 'addCourse']);
            Route::delete('/bundle-items/{bundleItem}', [BundleController::class, 'removeCourse']);
            Route::put('/courses/{course}/bundle-items/reorder', [BundleController::class, 'reorder']);
        });
    });

    // Payment routes (no auth - called by Paymob)
    Route::post('/payments/webhook', [PaymentWebhookController::class, 'handle']);
    Route::get('/payments/callback', [PaymentWebhookController::class, 'callback']);

    // Payment verification (authenticated)
    Route::middleware('auth:sanctum')->post('/payments/verify', [PaymentWebhookController::class, 'verify']);
});
