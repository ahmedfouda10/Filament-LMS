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
use App\Http\Controllers\Api\V1\SubscriptionPlanController;
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
    Route::get('/courses/{slug}', [CourseController::class, 'show']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/instructors/{id}', [InstructorController::class, 'show']);
    Route::get('/subscription-plans', [SubscriptionPlanController::class, 'index']);
    Route::post('/contact', [ContactController::class, 'store']);

    // ==========================================
    // AUTHENTICATED ROUTES
    // ==========================================
    Route::middleware('auth:sanctum')->group(function () {

        // User Profile
        Route::put('/user/profile', [ProfileController::class, 'update']);
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
        });
    });
});
