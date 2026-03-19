<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\ApplyPromoRequest;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Course;
use App\Models\PromoCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $cart = $this->getOrCreateCart($request->user());
        $cart->load('items.course.instructor', 'promoCode');

        return response()->json([
            'data' => new CartResource($cart),
        ]);
    }

    public function addItem(AddToCartRequest $request): JsonResponse
    {
        $user = $request->user();
        $cart = $this->getOrCreateCart($user);

        // Check course exists and is published
        $course = Course::where('id', $request->course_id)
            ->where('status', 'published')
            ->firstOrFail();

        // Check user is not already enrolled in this course
        $isEnrolled = $user->enrollments()
            ->where('course_id', $course->id)
            ->exists();

        if ($isEnrolled) {
            return response()->json([
                'message' => 'You are already enrolled in this course.',
            ], 422);
        }

        // Check course is not already in cart
        $existsInCart = $cart->items()
            ->where('course_id', $course->id)
            ->exists();

        if ($existsInCart) {
            return response()->json([
                'message' => 'This course is already in your cart.',
            ], 422);
        }

        // Add CartItem with course price
        CartItem::create([
            'cart_id' => $cart->id,
            'course_id' => $course->id,
            'price' => $course->price,
        ]);

        $cart->load('items.course.instructor', 'promoCode');

        return response()->json([
            'data' => new CartResource($cart),
            'message' => 'Course added to cart.',
        ], 201);
    }

    public function removeItem(int $id, Request $request): JsonResponse
    {
        $cart = $this->getOrCreateCart($request->user());

        $cartItem = $cart->items()->where('id', $id)->firstOrFail();
        $cartItem->delete();

        $cart->load('items.course.instructor', 'promoCode');

        return response()->json([
            'data' => new CartResource($cart),
            'message' => 'Item removed from cart.',
        ]);
    }

    public function clear(Request $request): JsonResponse
    {
        $cart = $this->getOrCreateCart($request->user());
        $cart->items()->delete();
        $cart->update(['promo_code_id' => null]);

        return response()->json([
            'message' => 'Cart cleared successfully.',
        ]);
    }

    public function applyPromo(ApplyPromoRequest $request): JsonResponse
    {
        $cart = $this->getOrCreateCart($request->user());
        $cart->load('items');

        // Find promo code by code
        $promoCode = PromoCode::where('code', $request->code)->first();

        if (!$promoCode) {
            return response()->json([
                'message' => 'Invalid promo code.',
            ], 422);
        }

        // Validate it's active
        if (!$promoCode->is_active) {
            return response()->json([
                'message' => 'This promo code is no longer active.',
            ], 422);
        }

        // Validate not expired
        if ($promoCode->expires_at && $promoCode->expires_at->isPast()) {
            return response()->json([
                'message' => 'This promo code has expired.',
            ], 422);
        }

        // Validate not exceeded max_uses
        if ($promoCode->max_uses && $promoCode->used_count >= $promoCode->max_uses) {
            return response()->json([
                'message' => 'This promo code has reached its maximum usage limit.',
            ], 422);
        }

        // Check minimum_purchase if applicable
        $cartTotal = $cart->items->sum('price');
        if ($promoCode->minimum_purchase && $cartTotal < $promoCode->minimum_purchase) {
            return response()->json([
                'message' => "Minimum purchase of {$promoCode->minimum_purchase} is required for this promo code.",
            ], 422);
        }

        // Set cart promo_code_id
        $cart->update(['promo_code_id' => $promoCode->id]);

        $cart->load('items.course.instructor', 'promoCode');

        return response()->json([
            'data' => new CartResource($cart),
            'message' => 'Promo code applied successfully.',
        ]);
    }

    public function removePromo(Request $request): JsonResponse
    {
        $cart = $this->getOrCreateCart($request->user());
        $cart->update(['promo_code_id' => null]);

        $cart->load('items.course.instructor', 'promoCode');

        return response()->json([
            'data' => new CartResource($cart),
            'message' => 'Promo code removed.',
        ]);
    }

    private function getOrCreateCart($user): Cart
    {
        return Cart::firstOrCreate(['user_id' => $user->id]);
    }
}
