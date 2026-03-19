# SPC Online Academy - REST API Documentation

---

## Table of Contents

1. [Base URL & Authentication](#1-base-url--authentication)
2. [Auth Endpoints](#2-auth-endpoints)
3. [Public Endpoints](#3-public-endpoints)
4. [Student Endpoints](#4-student-endpoints)
5. [Instructor Endpoints](#5-instructor-endpoints)
6. [Cart & Checkout](#6-cart--checkout)
7. [Quiz Endpoints](#7-quiz-endpoints)
8. [User Profile](#8-user-profile)

---

## 1. Base URL & Authentication

### Base URL

```
http://your-domain.com/api/v1
```

All endpoints are prefixed with `/api/v1`.

### Authentication

This API uses **Laravel Sanctum** token-based authentication. After a successful login or registration, the API returns a `token` field. Include this token in the `Authorization` header for all protected endpoints.

**Header format:**

```
Authorization: Bearer {token}
```

### Test Credentials

| Role       | Email                      | Password   |
|------------|----------------------------|------------|
| Admin      | admin@spc-academy.com      | password   |
| Instructor | ahmed@spc-academy.com      | password   |
| Student    | Any factory-seeded student  | password   |

### Pagination Format

Paginated endpoints return data in this format:

```json
{
  "data": [ ... ],
  "links": {
    "first": "http://...?page=1",
    "last": "http://...?page=5",
    "prev": null,
    "next": "http://...?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "links": [ ... ],
    "path": "http://...",
    "per_page": 10,
    "to": 10,
    "total": 50
  }
}
```

### Standard Error Responses

**Validation Error (422):**

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field_name": [
      "The field_name is required."
    ]
  }
}
```

**Unauthenticated (401):**

```json
{
  "message": "Unauthenticated."
}
```

**Forbidden (403):**

```json
{
  "message": "You do not have permission to modify this resource."
}
```

**Not Found (404):**

```json
{
  "message": "No query results for model [Model]."
}
```

---

## 2. Auth Endpoints

### Register
`POST /api/v1/auth/register`

**Auth:** Not Required

**Request Body:**

| Field                 | Type   | Required | Description                                |
|-----------------------|--------|----------|--------------------------------------------|
| name                  | string | Yes      | Full name (max 255 chars)                  |
| email                 | string | Yes      | Valid email, must be unique                |
| phone                 | string | Yes      | Phone number (max 20 chars)                |
| password              | string | Yes      | Minimum 8 characters                       |
| password_confirmation | string | Yes      | Must match password                        |
| role                  | string | No       | `student` or `instructor` (default: `student`) |

**Response (201):**

```json
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "avatar": null,
    "role": "student",
    "is_active": true,
    "created_at": "2026-03-19T10:00:00.000000Z"
  },
  "token": "1|abc123xyz...",
  "message": "Registration successful."
}
```

**Error Response (422):**

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email has already been taken."],
    "password": ["The password field confirmation does not match."]
  }
}
```

---

### Login
`POST /api/v1/auth/login`

**Auth:** Not Required
**Rate Limited:** Yes (throttle:login)

Supports login via email **or** phone number in the `email` field. The `role` must match the user's actual role in the database.

**Request Body:**

| Field    | Type   | Required | Description                              |
|----------|--------|----------|------------------------------------------|
| email    | string | Yes      | Email address or phone number            |
| password | string | Yes      | Minimum 8 characters                     |
| role     | string | Yes      | `student` or `instructor`                |

**Response (200):**

```json
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "avatar": null,
    "role": "student",
    "is_active": true,
    "created_at": "2026-03-19T10:00:00.000000Z"
  },
  "token": "2|def456uvw...",
  "message": "Login successful."
}
```

**Error Response (422):**

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The provided credentials are incorrect."],
    "role": ["Invalid role for this account."]
  }
}
```

> **Business Logic:** If the account's `is_active` flag is `false`, login is rejected with "Your account has been deactivated."

---

### Logout
`POST /api/v1/auth/logout`

**Auth:** Required
**Headers:** `Authorization: Bearer {token}`

**Request Body:** None

**Response (200):**

```json
{
  "message": "Logged out successfully."
}
```

---

### Get Current User
`GET /api/v1/auth/user`

**Auth:** Required
**Headers:** `Authorization: Bearer {token}`

Returns the authenticated user with their instructor profile loaded (if applicable).

**Response (200):**

```json
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "avatar": "/storage/avatars/abc123.jpg",
    "role": "student",
    "is_active": true,
    "created_at": "2026-03-19T10:00:00.000000Z"
  }
}
```

---

### Refresh Token
`POST /api/v1/auth/refresh`

**Auth:** Required
**Headers:** `Authorization: Bearer {token}`

Deletes the current token and issues a new one.

**Request Body:** None

**Response (200):**

```json
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "avatar": null,
    "role": "student",
    "is_active": true,
    "created_at": "2026-03-19T10:00:00.000000Z"
  },
  "token": "3|ghi789rst..."
}
```

---

### Forgot Password
`POST /api/v1/auth/forgot-password`

**Auth:** Not Required

Sends a password reset link to the provided email address.

**Request Body:**

| Field | Type   | Required | Description                          |
|-------|--------|----------|--------------------------------------|
| email | string | Yes      | Must exist in users table            |

**Response (200):**

```json
{
  "message": "Password reset link sent."
}
```

**Error Response (422):**

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["We can't find a user with that email address."]
  }
}
```

---

### Reset Password
`POST /api/v1/auth/reset-password`

**Auth:** Not Required

Resets the password using the token received via email. All existing tokens for the user are revoked after a successful reset.

**Request Body:**

| Field                 | Type   | Required | Description                     |
|-----------------------|--------|----------|---------------------------------|
| token                 | string | Yes      | Reset token from email link     |
| email                 | string | Yes      | Must exist in users table       |
| password              | string | Yes      | New password, minimum 8 chars   |
| password_confirmation | string | Yes      | Must match password             |

**Response (200):**

```json
{
  "message": "Password reset successful."
}
```

**Error Response (422):**

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["This password reset token is invalid."]
  }
}
```

---

## 3. Public Endpoints

### List Courses
`GET /api/v1/courses`

**Auth:** Not Required

Returns a paginated list of published courses with filtering, searching, and sorting.

**Query Parameters:**

| Param     | Type    | Default  | Description                                                     |
|-----------|---------|----------|-----------------------------------------------------------------|
| per_page  | integer | 9        | Items per page                                                  |
| category  | string  | -        | Filter by category slug                                         |
| search    | string  | -        | Search in title and description                                 |
| level     | string  | -        | `beginner`, `intermediate`, or `advanced`                       |
| min_price | numeric | -        | Minimum price filter                                            |
| max_price | numeric | -        | Maximum price filter                                            |
| rating    | numeric | -        | Minimum average rating filter                                   |
| is_bundle | boolean | -        | Filter bundle courses (`true` or `false`)                       |
| sort_by   | string  | newest   | `newest`, `popular`, `highest_rated`, `price_low`, `price_high` |

**Response (200):**

```json
{
  "data": [
    {
      "id": 1,
      "title": "Introduction to Web Development",
      "slug": "introduction-to-web-development",
      "short_description": "Learn HTML, CSS, and JavaScript basics",
      "image": "/storage/courses/web-dev.jpg",
      "price": 49.99,
      "original_price": 99.99,
      "level": "beginner",
      "language": "English",
      "is_bundle": false,
      "is_featured": true,
      "average_rating": "4.50",
      "students_count": 150,
      "total_duration": 1200,
      "instructor": {
        "id": 2,
        "name": "Ahmed Instructor",
        "avatar": "/storage/avatars/ahmed.jpg"
      },
      "category": {
        "id": 1,
        "name": "Web Development",
        "slug": "web-development",
        "icon": "code",
        "description": "Build modern websites"
      }
    }
  ],
  "links": { "first": "...", "last": "...", "prev": null, "next": "..." },
  "meta": { "current_page": 1, "per_page": 9, "total": 25, "..." : "..." }
}
```

---

### Featured Courses
`GET /api/v1/courses/featured`

**Auth:** Not Required

Returns up to 8 featured published courses, ordered by newest first.

**Response (200):**

```json
{
  "data": [
    {
      "id": 1,
      "title": "Introduction to Web Development",
      "slug": "introduction-to-web-development",
      "short_description": "Learn HTML, CSS, and JavaScript basics",
      "image": "/storage/courses/web-dev.jpg",
      "price": 49.99,
      "original_price": 99.99,
      "level": "beginner",
      "language": "English",
      "is_bundle": false,
      "is_featured": true,
      "average_rating": "4.50",
      "students_count": 150,
      "total_duration": 1200,
      "instructor": {
        "id": 2,
        "name": "Ahmed Instructor",
        "avatar": "/storage/avatars/ahmed.jpg"
      },
      "category": {
        "id": 1,
        "name": "Web Development",
        "slug": "web-development",
        "icon": "code",
        "description": "Build modern websites"
      }
    }
  ]
}
```

---

### Course Detail
`GET /api/v1/courses/{slug}`

**Auth:** Not Required

Returns full course detail by slug, including modules, lessons, reviews, and full instructor info.

**URL Parameters:**

| Param | Type   | Description   |
|-------|--------|---------------|
| slug  | string | Course slug   |

**Response (200):**

```json
{
  "data": {
    "id": 1,
    "title": "Introduction to Web Development",
    "slug": "introduction-to-web-development",
    "short_description": "Learn HTML, CSS, and JavaScript basics",
    "image": "/storage/courses/web-dev.jpg",
    "price": 49.99,
    "original_price": 99.99,
    "level": "beginner",
    "language": "English",
    "is_bundle": false,
    "is_featured": true,
    "average_rating": "4.50",
    "students_count": 150,
    "total_duration": 1200,
    "description": "Full course description...",
    "requirements": ["Basic computer knowledge", "A web browser"],
    "learning_outcomes": ["Build a website from scratch", "Understand CSS layouts"],
    "tags": ["html", "css", "javascript"],
    "reviews_count": 42,
    "instructor": {
      "id": 2,
      "name": "Ahmed Instructor",
      "avatar": "/storage/avatars/ahmed.jpg",
      "profile": {
        "bio": "Experienced web developer...",
        "specialization": "Full-Stack Development",
        "years_of_experience": 10,
        "qualifications": "MSc Computer Science",
        "education": "MIT",
        "expertise": ["JavaScript", "PHP", "Python"],
        "social_links": { "linkedin": "...", "twitter": "..." }
      },
      "courses_count": 5,
      "total_students": 500,
      "average_rating": 4.5
    },
    "category": {
      "id": 1,
      "name": "Web Development",
      "slug": "web-development",
      "icon": "code",
      "description": "Build modern websites"
    },
    "modules": [
      {
        "id": 1,
        "title": "Getting Started",
        "sort_order": 1,
        "lessons": [
          {
            "id": 1,
            "title": "Welcome to the Course",
            "type": "video",
            "duration_minutes": 10,
            "video_url": "https://...",
            "content": null,
            "is_free": true,
            "sort_order": 1
          }
        ]
      }
    ]
  }
}
```

---

### List Categories
`GET /api/v1/categories`

**Auth:** Not Required

Returns all categories with their course count, ordered by `sort_order`.

**Response (200):**

```json
{
  "data": [
    {
      "id": 1,
      "name": "Web Development",
      "slug": "web-development",
      "icon": "code",
      "description": "Build modern websites",
      "courses_count": 12
    }
  ]
}
```

---

### Instructor Profile
`GET /api/v1/instructors/{id}`

**Auth:** Not Required

Returns public instructor profile with their published courses.

**URL Parameters:**

| Param | Type    | Description    |
|-------|---------|----------------|
| id    | integer | Instructor ID  |

**Response (200):**

```json
{
  "data": {
    "id": 2,
    "name": "Ahmed Instructor",
    "avatar": "/storage/avatars/ahmed.jpg",
    "profile": {
      "bio": "Experienced web developer...",
      "specialization": "Full-Stack Development",
      "years_of_experience": 10,
      "qualifications": "MSc Computer Science",
      "education": "MIT",
      "expertise": ["JavaScript", "PHP"],
      "social_links": { "linkedin": "...", "twitter": "..." }
    },
    "courses_count": 5,
    "total_students": 500,
    "average_rating": 4.5
  }
}
```

---

### List Subscription Plans
`GET /api/v1/subscription-plans`

**Auth:** Not Required

Returns all active subscription plans ordered by `duration_months`.

**Response (200):**

```json
{
  "data": [
    {
      "id": 1,
      "name": "Monthly Plan",
      "description": "Access all courses for one month",
      "duration_months": 1,
      "price_per_month": 29.99,
      "total_price": 29.99,
      "savings_percentage": 0.0,
      "features": ["Unlimited course access", "Certificate generation"],
      "is_popular": false
    }
  ]
}
```

---

### Submit Contact Message
`POST /api/v1/contact`

**Auth:** Not Required

**Request Body:**

| Field   | Type   | Required | Description            |
|---------|--------|----------|------------------------|
| name    | string | Yes      | Sender name (max 255)  |
| email   | string | Yes      | Valid email (max 255)  |
| subject | string | No       | Subject line (max 255) |
| message | string | Yes      | Message body           |

**Response (201):**

```json
{
  "message": "Your message has been sent successfully. We will get back to you soon."
}
```

---

## 4. Student Endpoints

All student endpoints require authentication and the `student` role.

**Headers:** `Authorization: Bearer {token}`

---

### Student Dashboard
`GET /api/v1/student/dashboard`

**Auth:** Required
**Role:** student

Returns dashboard statistics and up to 3 in-progress courses for "continue learning."

**Response (200):**

```json
{
  "data": {
    "enrolled_courses": 5,
    "active_subscriptions": 1,
    "completed_courses": 2,
    "certificates_earned": 2,
    "continue_learning": [
      {
        "id": 1,
        "course": {
          "id": 3,
          "title": "React Fundamentals",
          "slug": "react-fundamentals",
          "image": "/storage/courses/react.jpg",
          "price": 39.99,
          "instructor_name": "Ahmed Instructor"
        },
        "progress_percentage": 45.5,
        "enrolled_at": "2026-01-15T10:00:00.000000Z",
        "completed_at": null,
        "last_accessed_lesson_id": 12
      }
    ]
  }
}
```

---

### List My Courses
`GET /api/v1/student/courses`

**Auth:** Required
**Role:** student

Returns paginated list of enrolled courses with filtering and search.

**Query Parameters:**

| Param    | Type    | Default | Description                                    |
|----------|---------|---------|------------------------------------------------|
| per_page | integer | 10      | Items per page                                 |
| filter   | string  | all     | `all`, `in-progress`, or `completed`           |
| search   | string  | -       | Search by course title                         |

**Response (200):**

```json
{
  "data": [
    {
      "id": 1,
      "course": {
        "id": 3,
        "title": "React Fundamentals",
        "slug": "react-fundamentals",
        "image": "/storage/courses/react.jpg",
        "price": 39.99,
        "instructor_name": "Ahmed Instructor"
      },
      "progress_percentage": 45.5,
      "enrolled_at": "2026-01-15T10:00:00.000000Z",
      "completed_at": null,
      "last_accessed_lesson_id": 12
    }
  ],
  "links": { "..." : "..." },
  "meta": { "current_page": 1, "per_page": 10, "total": 5, "..." : "..." }
}
```

---

### Course Player
`GET /api/v1/student/courses/{id}/player`

**Auth:** Required
**Role:** student

Returns the full course content for the player view, including all modules, lessons, and the student's completion status for each lesson. The student must be enrolled in the course.

**URL Parameters:**

| Param | Type    | Description |
|-------|---------|-------------|
| id    | integer | Course ID   |

**Response (200):**

```json
{
  "data": {
    "course": {
      "id": 3,
      "title": "React Fundamentals",
      "slug": "react-fundamentals",
      "thumbnail": "/storage/courses/react.jpg"
    },
    "enrollment": {
      "id": 1,
      "progress_percentage": 45.5,
      "completed_at": null,
      "enrolled_at": "2026-01-15T10:00:00.000000Z"
    },
    "modules": [
      {
        "id": 1,
        "title": "Getting Started",
        "sort_order": 1,
        "lessons": [
          {
            "id": 1,
            "title": "Introduction",
            "type": "video",
            "duration_minutes": 10,
            "video_url": "https://...",
            "content": null,
            "is_free": true,
            "sort_order": 1,
            "is_completed": true,
            "quiz": null
          },
          {
            "id": 5,
            "title": "Module Quiz",
            "type": "quiz",
            "duration_minutes": 15,
            "video_url": null,
            "content": null,
            "is_free": false,
            "sort_order": 3,
            "is_completed": false,
            "quiz": {
              "id": 1,
              "title": "Getting Started Quiz",
              "passing_score": 70,
              "max_attempts": 3,
              "time_limit": 15
            }
          }
        ]
      }
    ],
    "completed_lesson_ids": [1, 2, 3]
  }
}
```

---

### Mark Lesson Complete
`POST /api/v1/student/lessons/{id}/complete`

**Auth:** Required
**Role:** student

Marks a lesson as completed for the authenticated student. Automatically recalculates course progress. If all lessons are completed and all quizzes are passed, a certificate is generated and the enrollment is marked as completed.

**URL Parameters:**

| Param | Type    | Description |
|-------|---------|-------------|
| id    | integer | Lesson ID   |

**Request Body:** None

**Response (200):**

```json
{
  "data": {
    "lesson_id": 5,
    "is_completed": true,
    "progress_percentage": 80.0,
    "total_lessons": 10,
    "completed_lessons": 8,
    "course_completed": false
  }
}
```

> **Business Logic:**
> - The student must be enrolled in the course that contains the lesson.
> - Progress is recalculated as `(completed_lessons / total_lessons) * 100`.
> - When progress reaches 100%, the system checks if all quizzes in the course have been passed.
> - If all quizzes are passed, a certificate is generated with a `CERT-XXXXXXXX` number and a 2-year expiry.
> - The enrollment's `completed_at` is set only when both conditions are met (all lessons done + all quizzes passed).

---

### Get Course Progress
`GET /api/v1/student/courses/{id}/progress`

**Auth:** Required
**Role:** student

Returns detailed progress information for a specific course enrollment.

**URL Parameters:**

| Param | Type    | Description |
|-------|---------|-------------|
| id    | integer | Course ID   |

**Response (200):**

```json
{
  "data": {
    "course_id": 3,
    "progress_percentage": 60.0,
    "total_lessons": 10,
    "completed_count": 6,
    "completed_at": null,
    "lesson_completions": [
      {
        "lesson_id": 1,
        "lesson_title": "Introduction",
        "completed_at": "2026-02-01T14:30:00.000000Z"
      }
    ]
  }
}
```

---

### List Certificates
`GET /api/v1/student/certificates`

**Auth:** Required
**Role:** student

Returns all certificates earned by the authenticated student.

**Response (200):**

```json
{
  "data": [
    {
      "id": 1,
      "certificate_number": "CERT-AB12CD34",
      "student_name": "John Doe",
      "certificate_url": "https://...",
      "course": {
        "id": 3,
        "title": "React Fundamentals",
        "slug": "react-fundamentals",
        "image": "/storage/courses/react.jpg",
        "price": 39.99,
        "instructor_name": "Ahmed Instructor"
      },
      "issued_at": "2026-03-01T10:00:00.000000Z",
      "valid_until": "2028-03-01T10:00:00.000000Z"
    }
  ]
}
```

---

### Download Certificate
`GET /api/v1/student/certificates/{id}/download`

**Auth:** Required
**Role:** student

Returns certificate details with a download URL. Only accessible to the certificate owner.

**URL Parameters:**

| Param | Type    | Description    |
|-------|---------|----------------|
| id    | integer | Certificate ID |

**Response (200):**

```json
{
  "data": {
    "certificate_number": "CERT-AB12CD34",
    "student_name": "John Doe",
    "course_title": "React Fundamentals",
    "instructor_name": "Ahmed Instructor",
    "issued_at": "2026-03-01T10:00:00.000000Z",
    "expires_at": "2028-03-01T10:00:00.000000Z",
    "download_url": "http://your-domain.com/api/v1/certificates/1/pdf"
  }
}
```

---

### List Subscriptions
`GET /api/v1/student/subscriptions`

**Auth:** Required
**Role:** student

Returns all subscriptions for the authenticated student.

**Response (200):**

```json
{
  "data": [
    {
      "id": 1,
      "plan": {
        "id": 2,
        "name": "Annual Plan",
        "description": "12 months of access",
        "duration_months": 12,
        "price_per_month": 19.99,
        "total_price": 239.88,
        "savings_percentage": 33.0,
        "features": ["Unlimited course access", "Certificate generation"],
        "is_popular": true
      },
      "status": "active",
      "start_date": "2026-01-01",
      "end_date": "2027-01-01",
      "auto_renew": true
    }
  ]
}
```

---

### Create Subscription
`POST /api/v1/student/subscriptions`

**Auth:** Required
**Role:** student

**Request Body:**

| Field     | Type    | Required | Description                                  |
|-----------|---------|----------|----------------------------------------------|
| plan_id   | integer | Yes      | Must exist in `subscription_plans` table     |
| auto_renew| boolean | No       | Enable auto-renewal (default: `false`)       |

**Response (201):**

```json
{
  "data": {
    "id": 1,
    "plan": {
      "id": 2,
      "name": "Annual Plan",
      "description": "12 months of access",
      "duration_months": 12,
      "price_per_month": 19.99,
      "total_price": 239.88,
      "savings_percentage": 33.0,
      "features": ["Unlimited course access"],
      "is_popular": true
    },
    "status": "active",
    "start_date": "2026-03-19",
    "end_date": "2027-03-19",
    "auto_renew": false
  },
  "message": "Subscription created successfully."
}
```

> **Business Logic:** The subscription `end_date` is automatically calculated based on the plan's `duration_months`. Only active plans can be subscribed to.

---

### Update Subscription
`PUT /api/v1/student/subscriptions/{id}`

**Auth:** Required
**Role:** student

Updates subscription settings (currently only `auto_renew`).

**URL Parameters:**

| Param | Type    | Description     |
|-------|---------|-----------------|
| id    | integer | Subscription ID |

**Request Body:**

| Field      | Type    | Required | Description             |
|------------|---------|----------|-------------------------|
| auto_renew | boolean | Yes      | Enable/disable auto-renew |

**Response (200):**

```json
{
  "data": {
    "id": 1,
    "plan": { "..." : "..." },
    "status": "active",
    "start_date": "2026-01-01",
    "end_date": "2027-01-01",
    "auto_renew": true
  },
  "message": "Subscription updated successfully."
}
```

---

### Cancel Subscription
`POST /api/v1/student/subscriptions/{id}/cancel`

**Auth:** Required
**Role:** student

Cancels an active subscription. Sets status to `cancelled` and disables auto-renew.

**URL Parameters:**

| Param | Type    | Description     |
|-------|---------|-----------------|
| id    | integer | Subscription ID |

**Request Body:** None

**Response (200):**

```json
{
  "data": {
    "id": 1,
    "plan": { "..." : "..." },
    "status": "cancelled",
    "start_date": "2026-01-01",
    "end_date": "2027-01-01",
    "auto_renew": false
  },
  "message": "Subscription cancelled successfully."
}
```

---

### List Orders
`GET /api/v1/student/orders`

**Auth:** Required
**Role:** student

Returns paginated order history for the authenticated student.

**Query Parameters:**

| Param    | Type    | Default | Description    |
|----------|---------|---------|----------------|
| per_page | integer | 10      | Items per page |

**Response (200):**

```json
{
  "data": [
    {
      "id": 1,
      "order_number": "ORD-AB12CD34",
      "subtotal": 89.98,
      "discount": 9.00,
      "total": 80.98,
      "payment_method": "credit_card",
      "status": "completed",
      "paid_at": "2026-03-19T10:00:00.000000Z",
      "created_at": "2026-03-19T10:00:00.000000Z",
      "items": [
        {
          "id": 1,
          "course_id": 3,
          "title": "React Fundamentals",
          "instructor_name": "Ahmed Instructor",
          "price": 39.99,
          "original_price": 59.99
        }
      ],
      "billing": {
        "street": "123 Main St",
        "city": "Cairo",
        "state": "Cairo",
        "country": "Egypt",
        "postal_code": "11511"
      }
    }
  ],
  "links": { "..." : "..." },
  "meta": { "current_page": 1, "per_page": 10, "total": 3, "..." : "..." }
}
```

---

### Order Detail
`GET /api/v1/student/orders/{orderNumber}`

**Auth:** Required
**Role:** student

**URL Parameters:**

| Param       | Type   | Description                    |
|-------------|--------|--------------------------------|
| orderNumber | string | Order number (e.g. ORD-AB12CD34) |

**Response (200):**

```json
{
  "data": {
    "id": 1,
    "order_number": "ORD-AB12CD34",
    "subtotal": 89.98,
    "discount": 9.00,
    "total": 80.98,
    "payment_method": "credit_card",
    "status": "completed",
    "paid_at": "2026-03-19T10:00:00.000000Z",
    "created_at": "2026-03-19T10:00:00.000000Z",
    "items": [ "..." ],
    "billing": { "..." : "..." }
  }
}
```

---

### Order Receipt
`GET /api/v1/student/orders/{orderNumber}/receipt`

**Auth:** Required
**Role:** student

Returns a receipt-formatted view of the order.

**URL Parameters:**

| Param       | Type   | Description                    |
|-------------|--------|--------------------------------|
| orderNumber | string | Order number (e.g. ORD-AB12CD34) |

**Response (200):**

```json
{
  "data": {
    "receipt_number": "ORD-AB12CD34",
    "date": "2026-03-19T10:00:00.000000Z",
    "student": {
      "name": "John Doe",
      "email": "john@example.com"
    },
    "items": [
      {
        "course_title": "React Fundamentals",
        "instructor_name": "Ahmed Instructor",
        "price": 39.99
      }
    ],
    "subtotal": 89.98,
    "discount": 9.00,
    "total": 80.98,
    "payment_method": "credit_card",
    "status": "completed"
  }
}
```

---

### List My Reviews
`GET /api/v1/student/reviews`

**Auth:** Required
**Role:** student

Returns all reviews submitted by the authenticated student.

**Response (200):**

```json
{
  "data": [
    {
      "id": 1,
      "rating": 5,
      "comment": "Excellent course",
      "created_at": "2026-03-19T10:00:00.000000Z",
      "course": {
        "id": 1,
        "title": "Introduction to Clinical Pharmacy",
        "slug": "introduction-to-clinical-pharmacy",
        "image": "/storage/courses/clinical-pharmacy.jpg"
      }
    }
  ]
}
```

---

### Submit/Update Review
`POST /api/v1/student/reviews`

**Auth:** Required
**Role:** student

Submits a new review or updates an existing review for a course. One review per course per student; sending again updates the existing review.

**Request Body:**

| Field     | Type    | Required | Description                       |
|-----------|---------|----------|-----------------------------------|
| course_id | integer | Yes      | Course ID (must be enrolled)      |
| rating    | integer | Yes      | 1-5 stars                         |
| comment   | string  | No       | Review text (max 1000)            |

**Response (201 - new review):**

```json
{
  "data": {
    "id": 1,
    "rating": 5,
    "comment": "Excellent course with real clinical cases",
    "created_at": "2026-03-19T10:00:00.000000Z",
    "course": {
      "id": 1,
      "title": "Introduction to Clinical Pharmacy",
      "slug": "introduction-to-clinical-pharmacy",
      "image": "/storage/courses/clinical-pharmacy.jpg"
    }
  },
  "message": "Review submitted successfully."
}
```

**Response (200 - updated review):**

```json
{
  "data": {
    "id": 1,
    "rating": 4,
    "comment": "Updated review text",
    "created_at": "2026-03-19T10:00:00.000000Z",
    "course": { "..." : "..." }
  },
  "message": "Review submitted successfully."
}
```

**Error Response (403):**

```json
{
  "message": "You must be enrolled in this course to leave a review."
}
```

> **Business Logic:** One review per course per student. Sending again updates the existing review.

---

### Delete Review
`DELETE /api/v1/student/reviews/{id}`

**Auth:** Required
**Role:** student

Deletes a review owned by the authenticated student.

**URL Parameters:**

| Param | Type    | Description |
|-------|---------|-------------|
| id    | integer | Review ID   |

**Response (200):**

```json
{
  "message": "Review deleted successfully."
}
```

---

## 5. Instructor Endpoints

All instructor endpoints require authentication and the `instructor` role.

**Headers:** `Authorization: Bearer {token}`

---

### Instructor Dashboard
`GET /api/v1/instructor/dashboard`

**Auth:** Required
**Role:** instructor

Returns comprehensive dashboard statistics including revenue, students, and recent reviews.

**Response (200):**

```json
{
  "data": {
    "total_revenue": 12500.00,
    "new_students_this_month": 23,
    "average_rating": 4.65,
    "active_courses": 5,
    "revenue_growth": 15.5,
    "current_month_revenue": 2300.00,
    "last_month_revenue": 1991.34,
    "recent_reviews": [
      {
        "id": 10,
        "student_name": "John Doe",
        "course_title": "React Fundamentals",
        "rating": 5,
        "comment": "Excellent course!",
        "date": "2026-03-18 14:30:00"
      }
    ]
  }
}
```

> **Business Logic:**
> - `total_revenue`: Sum of `net_amount` from sale transactions with `cleared` or `completed` status.
> - `revenue_growth`: Percentage change between current and previous month revenue.
> - `recent_reviews`: Latest 5 reviews across all instructor courses.

---

### List Instructor Courses
`GET /api/v1/instructor/courses`

**Auth:** Required
**Role:** instructor

Returns paginated list of all courses owned by the instructor (including unpublished).

**Query Parameters:**

| Param    | Type    | Default | Description    |
|----------|---------|---------|----------------|
| per_page | integer | 10      | Items per page |

**Response (200):**

```json
{
  "data": [
    {
      "id": 1,
      "title": "Introduction to Web Development",
      "slug": "introduction-to-web-development",
      "short_description": "Learn HTML, CSS, and JavaScript basics",
      "image": "/storage/courses/web-dev.jpg",
      "price": 49.99,
      "original_price": 99.99,
      "level": "beginner",
      "language": "English",
      "is_bundle": false,
      "is_featured": false,
      "average_rating": "4.50",
      "students_count": 150,
      "total_duration": 1200,
      "category": { "..." : "..." }
    }
  ],
  "links": { "..." : "..." },
  "meta": { "current_page": 1, "per_page": 10, "total": 5, "..." : "..." }
}
```

---

### Get Instructor Course Detail
`GET /api/v1/instructor/courses/{id}`

**Auth:** Required
**Role:** instructor

Returns full course detail including modules, lessons, and reviews. Only returns courses owned by the authenticated instructor.

**URL Parameters:**

| Param | Type    | Description |
|-------|---------|-------------|
| id    | integer | Course ID   |

**Response (200):**

```json
{
  "data": {
    "id": 1,
    "title": "Introduction to Web Development",
    "slug": "introduction-to-web-development",
    "short_description": "...",
    "description": "Full description...",
    "image": "...",
    "price": 49.99,
    "original_price": 99.99,
    "level": "beginner",
    "language": "English",
    "is_bundle": false,
    "is_featured": false,
    "average_rating": "4.50",
    "students_count": 150,
    "total_duration": 1200,
    "requirements": ["Basic computer knowledge"],
    "learning_outcomes": ["Build websites"],
    "tags": ["html", "css"],
    "reviews_count": 42,
    "category": { "..." : "..." },
    "instructor": { "..." : "..." },
    "modules": [
      {
        "id": 1,
        "title": "Getting Started",
        "sort_order": 1,
        "lessons": [ "..." ]
      }
    ]
  }
}
```

---

### Create Course
`POST /api/v1/instructor/courses`

**Auth:** Required
**Role:** instructor

**Request Body:**

| Field             | Type     | Required | Description                                     |
|-------------------|----------|----------|-------------------------------------------------|
| title             | string   | Yes      | Course title (max 255)                          |
| category_id       | integer  | Yes      | Must exist in `categories` table                |
| short_description | string   | No       | Brief description (max 500)                     |
| description       | string   | No       | Full description                                |
| image             | string   | No       | Image URL/path (max 500)                        |
| price             | numeric  | Yes      | Course price (min 0)                            |
| original_price    | numeric  | No       | Original price before discount (min 0)          |
| level             | string   | No       | `beginner`, `intermediate`, or `advanced`       |
| language          | string   | No       | Course language (max 100)                       |
| is_bundle         | boolean  | No       | Whether this is a bundle                        |
| requirements      | array    | No       | Array of requirement strings                    |
| learning_outcomes | array    | No       | Array of learning outcome strings               |
| tags              | array    | No       | Array of tag strings                            |

**Response (201):**

```json
{
  "data": {
    "id": 10,
    "title": "New Course",
    "slug": "new-course",
    "short_description": "A new course",
    "image": null,
    "price": 49.99,
    "original_price": null,
    "level": "beginner",
    "language": "English",
    "is_bundle": false,
    "is_featured": false,
    "average_rating": null,
    "students_count": null,
    "total_duration": null,
    "category": { "..." : "..." }
  },
  "message": "Course created successfully."
}
```

---

### Update Course
`PUT /api/v1/instructor/courses/{id}`

**Auth:** Required
**Role:** instructor

All fields are optional. Only the course owner can update it.

**URL Parameters:**

| Param | Type    | Description |
|-------|---------|-------------|
| id    | integer | Course ID   |

**Request Body:**

| Field             | Type     | Required | Description                                     |
|-------------------|----------|----------|-------------------------------------------------|
| title             | string   | No       | Course title (max 255)                          |
| category_id       | integer  | No       | Must exist in `categories` table                |
| short_description | string   | No       | Brief description (max 500)                     |
| description       | string   | No       | Full description                                |
| image             | string   | No       | Image URL/path (max 500)                        |
| price             | numeric  | No       | Course price (min 0)                            |
| original_price    | numeric  | No       | Original price before discount (min 0)          |
| level             | string   | No       | `beginner`, `intermediate`, or `advanced`       |
| language          | string   | No       | Course language (max 100)                       |
| is_bundle         | boolean  | No       | Whether this is a bundle                        |
| requirements      | array    | No       | Array of requirement strings                    |
| learning_outcomes | array    | No       | Array of learning outcome strings               |
| tags              | array    | No       | Array of tag strings                            |

**Response (200):**

```json
{
  "data": { "..." : "..." },
  "message": "Course updated successfully."
}
```

---

### Delete Course
`DELETE /api/v1/instructor/courses/{id}`

**Auth:** Required
**Role:** instructor

Deletes a course. Only the course owner can delete it.

**URL Parameters:**

| Param | Type    | Description |
|-------|---------|-------------|
| id    | integer | Course ID   |

**Request Body:** None

**Response (200):**

```json
{
  "message": "Course deleted successfully."
}
```

---

### Create Module
`POST /api/v1/instructor/courses/{id}/modules`

**Auth:** Required
**Role:** instructor

Creates a new module for a course. Only the course owner can add modules.

**URL Parameters:**

| Param | Type    | Description |
|-------|---------|-------------|
| id    | integer | Course ID   |

**Request Body:**

| Field      | Type    | Required | Description                  |
|------------|---------|----------|------------------------------|
| title      | string  | Yes      | Module title (max 255)       |
| sort_order | integer | No       | Display order (min 0)        |

**Response (201):**

```json
{
  "data": {
    "id": 5,
    "title": "Advanced Topics",
    "sort_order": 3,
    "course_id": 1,
    "created_at": "2026-03-19T10:00:00.000000Z",
    "updated_at": "2026-03-19T10:00:00.000000Z"
  },
  "message": "Module created successfully."
}
```

---

### Update Module
`PUT /api/v1/instructor/modules/{id}`

**Auth:** Required
**Role:** instructor

Updates a module. Only the course owner can modify it.

**URL Parameters:**

| Param | Type    | Description |
|-------|---------|-------------|
| id    | integer | Module ID   |

**Request Body:**

| Field      | Type    | Required | Description                  |
|------------|---------|----------|------------------------------|
| title      | string  | Yes      | Module title (max 255)       |
| sort_order | integer | No       | Display order (min 0)        |

**Response (200):**

```json
{
  "data": {
    "id": 5,
    "title": "Updated Module Title",
    "sort_order": 2,
    "course_id": 1,
    "created_at": "...",
    "updated_at": "..."
  },
  "message": "Module updated successfully."
}
```

---

### Delete Module
`DELETE /api/v1/instructor/modules/{id}`

**Auth:** Required
**Role:** instructor

Deletes a module. Only the course owner can delete it.

**URL Parameters:**

| Param | Type    | Description |
|-------|---------|-------------|
| id    | integer | Module ID   |

**Request Body:** None

**Response (200):**

```json
{
  "message": "Module deleted successfully."
}
```

---

### Create Lesson
`POST /api/v1/instructor/modules/{id}/lessons`

**Auth:** Required
**Role:** instructor

Creates a new lesson within a module. Only the course owner can add lessons.

**URL Parameters:**

| Param | Type    | Description |
|-------|---------|-------------|
| id    | integer | Module ID   |

**Request Body:**

| Field            | Type    | Required | Description                                       |
|------------------|---------|----------|---------------------------------------------------|
| title            | string  | Yes      | Lesson title (max 255)                            |
| type             | string  | No       | `video`, `quiz`, `assignment`, or `reading`       |
| duration_minutes | integer | No       | Lesson duration in minutes (min 0)                |
| video_url        | string  | No       | URL to video content (max 500)                    |
| content          | string  | No       | Text/HTML content for reading-type lessons        |
| is_free          | boolean | No       | Whether this lesson is free to preview            |
| sort_order       | integer | No       | Display order (min 0)                             |

**Response (201):**

```json
{
  "data": {
    "id": 15,
    "title": "New Lesson",
    "type": "video",
    "duration_minutes": 20,
    "video_url": "https://youtube.com/watch?v=...",
    "content": null,
    "is_free": false,
    "sort_order": 1,
    "module_id": 5,
    "created_at": "...",
    "updated_at": "..."
  },
  "message": "Lesson created successfully."
}
```

---

### Update Lesson
`PUT /api/v1/instructor/lessons/{id}`

**Auth:** Required
**Role:** instructor

Updates a lesson. Only the course owner can modify it.

**URL Parameters:**

| Param | Type    | Description |
|-------|---------|-------------|
| id    | integer | Lesson ID   |

**Request Body:**

| Field            | Type    | Required | Description                                       |
|------------------|---------|----------|---------------------------------------------------|
| title            | string  | Yes      | Lesson title (max 255)                            |
| type             | string  | No       | `video`, `quiz`, `assignment`, or `reading`       |
| duration_minutes | integer | No       | Lesson duration in minutes (min 0)                |
| video_url        | string  | No       | URL to video content (max 500)                    |
| content          | string  | No       | Text/HTML content                                 |
| is_free          | boolean | No       | Whether this lesson is free to preview            |
| sort_order       | integer | No       | Display order (min 0)                             |

**Response (200):**

```json
{
  "data": { "..." : "..." },
  "message": "Lesson updated successfully."
}
```

---

### Delete Lesson
`DELETE /api/v1/instructor/lessons/{id}`

**Auth:** Required
**Role:** instructor

Deletes a lesson. Only the course owner can delete it.

**URL Parameters:**

| Param | Type    | Description |
|-------|---------|-------------|
| id    | integer | Lesson ID   |

**Request Body:** None

**Response (200):**

```json
{
  "message": "Lesson deleted successfully."
}
```

---

### Revenue Overview
`GET /api/v1/instructor/revenue`

**Auth:** Required
**Role:** instructor

Returns the instructor's financial summary.

**Response (200):**

```json
{
  "data": {
    "available_balance": 3500.00,
    "pending_clearance": 800.00,
    "lifetime_earnings": 12500.00
  }
}
```

> **Business Logic:**
> - `available_balance` = sum of `net_amount` for cleared sales MINUS sum of `amount` for pending/completed payouts.
> - `pending_clearance` = sum of `net_amount` for pending sales.
> - `lifetime_earnings` = sum of all sale `net_amount` values (all statuses).
> - The platform takes a **20% fee** on each sale. The `net_amount` is 80% of the sale price.

---

### List Transactions
`GET /api/v1/instructor/transactions`

**Auth:** Required
**Role:** instructor

Returns paginated list of the instructor's transactions with optional filtering.

**Query Parameters:**

| Param    | Type    | Default | Description                                          |
|----------|---------|---------|------------------------------------------------------|
| per_page | integer | 15      | Items per page                                       |
| filter   | string  | all     | `all`, `sales`, or `payouts`                         |
| period   | string  | -       | `current_month`, `last_month`, or `year_to_date`     |

**Response (200):**

```json
{
  "data": [
    {
      "id": 1,
      "transaction_number": "TXN-AB1CD",
      "type": "sale",
      "course": {
        "id": 3,
        "title": "React Fundamentals",
        "slug": "react-fundamentals",
        "image": "...",
        "price": 39.99,
        "instructor_name": "Ahmed Instructor"
      },
      "amount": 39.99,
      "platform_fee": 8.00,
      "net_amount": 31.99,
      "status": "cleared",
      "payout_method": null,
      "created_at": "2026-03-15T10:00:00.000000Z"
    }
  ],
  "links": { "..." : "..." },
  "meta": { "current_page": 1, "per_page": 15, "total": 50, "..." : "..." }
}
```

---

### Request Payout
`POST /api/v1/instructor/payout-request`

**Auth:** Required
**Role:** instructor

Submits a payout request. The requested amount must not exceed the available balance.

**Request Body:**

| Field         | Type    | Required | Description                                |
|---------------|---------|----------|--------------------------------------------|
| amount        | numeric | Yes      | Payout amount (min 1)                      |
| payout_method | string  | Yes      | Payment method (e.g. "bank_transfer", max 100) |

**Response (201):**

```json
{
  "data": {
    "id": 25,
    "transaction_number": "TXN-XY9ZW",
    "type": "payout",
    "course": null,
    "amount": 500.00,
    "platform_fee": 0.0,
    "net_amount": 500.00,
    "status": "pending",
    "payout_method": "bank_transfer",
    "created_at": "2026-03-19T10:00:00.000000Z"
  },
  "message": "Payout request submitted successfully."
}
```

**Error Response (422) - Insufficient balance:**

```json
{
  "message": "Requested amount exceeds your available balance.",
  "available_balance": 350.00
}
```

---

### Reorder Modules
`PUT /api/v1/instructor/courses/{id}/modules/reorder`

**Auth:** Required
**Role:** instructor

Reorders the modules within a course. Only the course owner can reorder.

**URL Parameters:**

| Param | Type    | Description |
|-------|---------|-------------|
| id    | integer | Course ID   |

**Request Body:**

```json
{
  "order": [3, 1, 2]
}
```

| Field | Type  | Required | Description                          |
|-------|-------|----------|--------------------------------------|
| order | array | Yes      | Array of module IDs in the new order |

**Response (200):**

```json
{
  "message": "Modules reordered successfully."
}
```

---

### Reorder Lessons
`PUT /api/v1/instructor/modules/{id}/lessons/reorder`

**Auth:** Required
**Role:** instructor

Reorders the lessons within a module. Only the course owner can reorder.

**URL Parameters:**

| Param | Type    | Description |
|-------|---------|-------------|
| id    | integer | Module ID   |

**Request Body:**

```json
{
  "order": [5, 3, 4, 6]
}
```

| Field | Type  | Required | Description                          |
|-------|-------|----------|--------------------------------------|
| order | array | Yes      | Array of lesson IDs in the new order |

**Response (200):**

```json
{
  "message": "Lessons reordered successfully."
}
```

---

### List Instructor Quizzes
`GET /api/v1/instructor/quizzes`

**Auth:** Required
**Role:** instructor

Returns a paginated list of quizzes for courses owned by the authenticated instructor.

**Query Parameters:**

| Param    | Type    | Default | Description    |
|----------|---------|---------|----------------|
| per_page | integer | 15      | Items per page |

**Response (200):**

```json
{
  "data": [
    {
      "id": 1,
      "course_id": 1,
      "lesson_id": 5,
      "title": "Module 1 Quiz",
      "passing_score": 70,
      "time_limit_minutes": 15,
      "max_attempts": 3,
      "created_at": "2026-03-19T10:00:00.000000Z",
      "updated_at": "2026-03-19T10:00:00.000000Z",
      "questions_count": 5,
      "course": { "id": 1, "title": "Introduction to Clinical Pharmacy" },
      "lesson": { "id": 5, "title": "Module Quiz" }
    }
  ],
  "links": { "..." : "..." },
  "meta": { "current_page": 1, "per_page": 15, "total": 3, "..." : "..." }
}
```

---

### Create Quiz
`POST /api/v1/instructor/quizzes`

**Auth:** Required
**Role:** instructor

Creates a new quiz for a course, optionally with inline questions and options.

**Request Body:**

| Field                             | Type    | Required | Description                                    |
|-----------------------------------|---------|----------|------------------------------------------------|
| course_id                         | integer | Yes      | Must exist in `courses` table (owned by you)   |
| lesson_id                         | integer | No       | Must exist in `lessons` table, belong to course |
| title                             | string  | Yes      | Quiz title (max 255)                           |
| passing_score                     | integer | No       | 1-100 (default: 70)                            |
| time_limit_minutes                | integer | No       | Time limit in minutes (min 1, default: 15)     |
| max_attempts                      | integer | No       | Maximum attempts allowed (min 1, default: 3)   |
| questions                         | array   | No       | Array of question objects (min 1 if provided)  |
| questions[].question_text         | string  | Yes*     | Question text (*required if questions provided)|
| questions[].explanation           | string  | No       | Explanation shown after answering              |
| questions[].options               | array   | Yes*     | Exactly 4 options (*required if questions)     |
| questions[].options[].label       | string  | Yes      | Option label: `A`, `B`, `C`, or `D`           |
| questions[].options[].text        | string  | Yes      | Option text                                    |
| questions[].options[].is_correct  | boolean | Yes      | Whether this is the correct answer             |

**Example Request Body (with inline questions):**

```json
{
  "course_id": 1,
  "lesson_id": 5,
  "title": "Module 1 Quiz",
  "passing_score": 70,
  "time_limit_minutes": 15,
  "max_attempts": 3,
  "questions": [
    {
      "question_text": "What is the first-line treatment for acute STEMI?",
      "explanation": "Primary PCI with dual antiplatelet therapy...",
      "options": [
        { "label": "A", "text": "Aspirin + Primary PCI", "is_correct": true },
        { "label": "B", "text": "Beta-blockers alone", "is_correct": false },
        { "label": "C", "text": "Calcium channel blockers", "is_correct": false },
        { "label": "D", "text": "Watchful waiting", "is_correct": false }
      ]
    }
  ]
}
```

**Response (201):**

```json
{
  "data": {
    "id": 1,
    "course_id": 1,
    "lesson_id": 5,
    "title": "Module 1 Quiz",
    "passing_score": 70,
    "time_limit_minutes": 15,
    "max_attempts": 3,
    "created_at": "2026-03-19T10:00:00.000000Z",
    "updated_at": "2026-03-19T10:00:00.000000Z",
    "questions": [
      {
        "id": 1,
        "question_text": "What is the first-line treatment for acute STEMI?",
        "explanation": "Primary PCI with dual antiplatelet therapy...",
        "sort_order": 1,
        "options": [
          { "id": 1, "option_label": "A", "option_text": "Aspirin + Primary PCI", "is_correct": true },
          { "id": 2, "option_label": "B", "option_text": "Beta-blockers alone", "is_correct": false },
          { "id": 3, "option_label": "C", "option_text": "Calcium channel blockers", "is_correct": false },
          { "id": 4, "option_label": "D", "option_text": "Watchful waiting", "is_correct": false }
        ]
      }
    ]
  },
  "message": "Quiz created successfully."
}
```

**Error Response (422):**

```json
{ "message": "Lesson does not belong to this course." }
```

**Error Response (403):**

```json
{ "message": "You do not have permission to manage this quiz." }
```

---

### Show Quiz
`GET /api/v1/instructor/quizzes/{id}`

**Auth:** Required
**Role:** instructor

Returns quiz details with all questions and options. Only accessible to the course owner.

**URL Parameters:**

| Param | Type    | Description |
|-------|---------|-------------|
| id    | integer | Quiz ID     |

**Response (200):**

```json
{
  "data": {
    "id": 1,
    "course_id": 1,
    "lesson_id": 5,
    "title": "Module 1 Quiz",
    "passing_score": 70,
    "time_limit_minutes": 15,
    "max_attempts": 3,
    "created_at": "...",
    "updated_at": "...",
    "course": { "id": 1, "title": "Introduction to Clinical Pharmacy" },
    "lesson": { "id": 5, "title": "Module Quiz" },
    "questions": [
      {
        "id": 1,
        "question_text": "What is the first-line treatment for acute STEMI?",
        "explanation": "Primary PCI with dual antiplatelet therapy...",
        "sort_order": 1,
        "options": [
          { "id": 1, "option_label": "A", "option_text": "Aspirin + Primary PCI", "is_correct": true },
          { "id": 2, "option_label": "B", "option_text": "Beta-blockers alone", "is_correct": false },
          { "id": 3, "option_label": "C", "option_text": "Calcium channel blockers", "is_correct": false },
          { "id": 4, "option_label": "D", "option_text": "Watchful waiting", "is_correct": false }
        ]
      }
    ]
  }
}
```

---

### Update Quiz
`PUT /api/v1/instructor/quizzes/{id}`

**Auth:** Required
**Role:** instructor

Updates quiz settings. Only the course owner can update.

**URL Parameters:**

| Param | Type    | Description |
|-------|---------|-------------|
| id    | integer | Quiz ID     |

**Request Body:**

| Field              | Type    | Required | Description                                    |
|--------------------|---------|----------|------------------------------------------------|
| title              | string  | No       | Quiz title (max 255)                           |
| lesson_id          | integer | No       | Lesson ID (nullable)                           |
| passing_score      | integer | No       | 1-100                                          |
| time_limit_minutes | integer | No       | Time limit in minutes (min 1)                  |
| max_attempts       | integer | No       | Maximum attempts allowed (min 1)               |

**Response (200):**

```json
{
  "data": {
    "id": 1,
    "course_id": 1,
    "title": "Updated Quiz Title",
    "passing_score": 80,
    "time_limit_minutes": 20,
    "max_attempts": 3,
    "questions": [ "..." ]
  },
  "message": "Quiz updated successfully."
}
```

---

### Delete Quiz
`DELETE /api/v1/instructor/quizzes/{id}`

**Auth:** Required
**Role:** instructor

Deletes a quiz. Only the course owner can delete.

**URL Parameters:**

| Param | Type    | Description |
|-------|---------|-------------|
| id    | integer | Quiz ID     |

**Request Body:** None

**Response (200):**

```json
{
  "message": "Quiz deleted successfully."
}
```

---

### Add Question to Quiz
`POST /api/v1/instructor/quizzes/{id}/questions`

**Auth:** Required
**Role:** instructor

Adds a question with 4 options to a quiz. Each question must have exactly 4 options (A, B, C, D) with exactly 1 correct answer.

**URL Parameters:**

| Param | Type    | Description |
|-------|---------|-------------|
| id    | integer | Quiz ID     |

**Request Body:**

| Field              | Type    | Required | Description                       |
|--------------------|---------|----------|-----------------------------------|
| question_text      | string  | Yes      | Question text                     |
| explanation        | string  | No       | Explanation shown after answering |
| sort_order         | integer | No       | Display order (min 0)             |
| options            | array   | Yes      | Exactly 4 options                 |
| options[].label    | string  | Yes      | `A`, `B`, `C`, or `D`            |
| options[].text     | string  | Yes      | Option text                       |
| options[].is_correct | boolean | Yes    | Whether this is the correct answer|

**Example Request Body:**

```json
{
  "question_text": "Which biomarker is most specific?",
  "explanation": "Troponin I is the most cardiac-specific...",
  "sort_order": 1,
  "options": [
    { "label": "A", "text": "CK-MB", "is_correct": false },
    { "label": "B", "text": "Troponin I", "is_correct": true },
    { "label": "C", "text": "LDH", "is_correct": false },
    { "label": "D", "text": "AST", "is_correct": false }
  ]
}
```

**Response (201):**

```json
{
  "data": {
    "id": 1,
    "question_text": "Which biomarker is most specific?",
    "explanation": "Troponin I is the most cardiac-specific...",
    "sort_order": 1,
    "options": [
      { "id": 1, "option_label": "A", "option_text": "CK-MB", "is_correct": false },
      { "id": 2, "option_label": "B", "option_text": "Troponin I", "is_correct": true },
      { "id": 3, "option_label": "C", "option_text": "LDH", "is_correct": false },
      { "id": 4, "option_label": "D", "option_text": "AST", "is_correct": false }
    ]
  },
  "message": "Question added successfully."
}
```

**Error Response (422):**

```json
{ "message": "Each question must have exactly one correct answer." }
```

> **Note:** Each question must have exactly 4 options (A, B, C, D) with exactly 1 correct answer.

---

### Update Question
`PUT /api/v1/instructor/questions/{id}`

**Auth:** Required
**Role:** instructor

Updates a question and optionally its options. Only the course owner can update.

**URL Parameters:**

| Param | Type    | Description  |
|-------|---------|--------------|
| id    | integer | Question ID  |

**Request Body:**

| Field              | Type    | Required | Description                       |
|--------------------|---------|----------|-----------------------------------|
| question_text      | string  | No       | Question text                     |
| explanation        | string  | No       | Explanation (nullable)            |
| sort_order         | integer | No       | Display order (min 0)             |
| options            | array   | No       | Exactly 4 options (replaces all)  |
| options[].label    | string  | Yes*     | `A`, `B`, `C`, or `D`            |
| options[].text     | string  | Yes*     | Option text                       |
| options[].is_correct | boolean | Yes*   | Whether this is the correct answer|

> *Required if `options` array is provided.

**Response (200):**

```json
{
  "data": {
    "id": 1,
    "question_text": "Updated question text?",
    "explanation": "Updated explanation...",
    "sort_order": 1,
    "options": [ "..." ]
  },
  "message": "Question updated successfully."
}
```

---

### Delete Question
`DELETE /api/v1/instructor/questions/{id}`

**Auth:** Required
**Role:** instructor

Deletes a question and its options. Only the course owner can delete.

**URL Parameters:**

| Param | Type    | Description  |
|-------|---------|--------------|
| id    | integer | Question ID  |

**Request Body:** None

**Response (200):**

```json
{
  "message": "Question deleted successfully."
}
```

---

## 6. Cart & Checkout

All cart and checkout endpoints require authentication (any role).

**Headers:** `Authorization: Bearer {token}`

---

### View Cart
`GET /api/v1/cart`

**Auth:** Required

Returns the current user's cart with items, promo code, and calculated totals.

**Response (200):**

```json
{
  "data": {
    "id": 1,
    "items": [
      {
        "id": 1,
        "course": {
          "id": 3,
          "title": "React Fundamentals",
          "slug": "react-fundamentals",
          "image": "/storage/courses/react.jpg",
          "price": 39.99,
          "instructor_name": "Ahmed Instructor"
        },
        "price": 39.99
      }
    ],
    "promo_code": {
      "code": "SAVE10",
      "discount_percentage": 10.0
    },
    "subtotal": 39.99,
    "discount": 4.00,
    "total": 35.99
  }
}
```

---

### Add Item to Cart
`POST /api/v1/cart/items`

**Auth:** Required

**Request Body:**

| Field     | Type    | Required | Description                      |
|-----------|---------|----------|----------------------------------|
| course_id | integer | Yes      | Must exist in `courses` table    |

**Response (201):**

```json
{
  "data": {
    "id": 1,
    "items": [ "..." ],
    "promo_code": null,
    "subtotal": 39.99,
    "discount": 0,
    "total": 39.99
  },
  "message": "Course added to cart."
}
```

**Error Response (422):**

```json
{
  "message": "You are already enrolled in this course."
}
```

```json
{
  "message": "This course is already in your cart."
}
```

> **Business Logic:**
> - The course must be published (`status = published`).
> - The user cannot add a course they are already enrolled in.
> - The user cannot add the same course twice.

---

### Remove Item from Cart
`DELETE /api/v1/cart/items/{id}`

**Auth:** Required

**URL Parameters:**

| Param | Type    | Description  |
|-------|---------|--------------|
| id    | integer | Cart item ID |

**Response (200):**

```json
{
  "data": { "..." : "..." },
  "message": "Item removed from cart."
}
```

---

### Clear Cart
`DELETE /api/v1/cart`

**Auth:** Required

Removes all items and the applied promo code from the cart.

**Request Body:** None

**Response (200):**

```json
{
  "message": "Cart cleared successfully."
}
```

---

### Apply Promo Code
`POST /api/v1/cart/promo`

**Auth:** Required

**Request Body:**

| Field | Type   | Required | Description            |
|-------|--------|----------|------------------------|
| code  | string | Yes      | Promo code (max 50)    |

**Response (200):**

```json
{
  "data": {
    "id": 1,
    "items": [ "..." ],
    "promo_code": {
      "code": "SAVE10",
      "discount_percentage": 10.0
    },
    "subtotal": 89.98,
    "discount": 9.00,
    "total": 80.98
  },
  "message": "Promo code applied successfully."
}
```

**Error Responses (422):**

```json
{ "message": "Invalid promo code." }
```

```json
{ "message": "This promo code is no longer active." }
```

```json
{ "message": "This promo code has expired." }
```

```json
{ "message": "This promo code has reached its maximum usage limit." }
```

```json
{ "message": "Minimum purchase of 50.00 is required for this promo code." }
```

> **Business Logic:** The promo code is validated for: existence, active status, expiry date, max usage count, and minimum purchase amount.

---

### Remove Promo Code
`DELETE /api/v1/cart/promo`

**Auth:** Required

**Request Body:** None

**Response (200):**

```json
{
  "data": { "..." : "..." },
  "message": "Promo code removed."
}
```

---

### Process Checkout
`POST /api/v1/checkout`

**Auth:** Required

Processes the cart checkout. Creates an order, enrollments for each course, and instructor transactions. Then clears the cart.

**Request Body:**

| Field               | Type   | Required | Description                                                      |
|---------------------|--------|----------|------------------------------------------------------------------|
| payment_method      | string | Yes      | `credit_card`, `mobile_wallet`, `bank_transfer`, or `installment`|
| billing_street      | string | No       | Street address (max 255)                                         |
| billing_city        | string | No       | City (max 100)                                                   |
| billing_state       | string | No       | State/Province (max 100)                                         |
| billing_country     | string | No       | Country (max 100)                                                |
| billing_postal_code | string | No       | Postal/ZIP code (max 20)                                         |

**Response (201):**

```json
{
  "data": {
    "id": 5,
    "order_number": "ORD-AB12CD34",
    "subtotal": 89.98,
    "discount": 9.00,
    "total": 80.98,
    "payment_method": "credit_card",
    "status": "completed",
    "paid_at": "2026-03-19T10:00:00.000000Z",
    "created_at": "2026-03-19T10:00:00.000000Z",
    "items": [
      {
        "id": 1,
        "course_id": 3,
        "title": "React Fundamentals",
        "instructor_name": "Ahmed Instructor",
        "price": 39.99,
        "original_price": null
      },
      {
        "id": 2,
        "course_id": 7,
        "title": "Node.js Masterclass",
        "instructor_name": "Ahmed Instructor",
        "price": 49.99,
        "original_price": null
      }
    ],
    "billing": {
      "street": "123 Main St",
      "city": "Cairo",
      "state": "Cairo",
      "country": "Egypt",
      "postal_code": "11511"
    }
  },
  "message": "Checkout completed successfully."
}
```

**Error Response (422):**

```json
{ "message": "Your cart is empty." }
```

```json
{ "message": "You are already enrolled in one or more courses in your cart." }
```

> **Business Logic (executed in a database transaction):**
> 1. Validates the cart is not empty.
> 2. Validates the user is not already enrolled in any of the cart's courses.
> 3. Calculates subtotal from cart items.
> 4. Applies promo code discount (percentage-based) if present, then increments the promo's `used_count`.
> 5. Creates the `Order` record with status `completed` and `paid_at` set to now.
> 6. Creates `OrderItem` records with snapshot data (course title, instructor name, price).
> 7. Creates `Enrollment` records for each course (progress starts at 0%).
> 8. Creates `InstructorTransaction` records for each course: **20% platform fee**, net amount = 80% of sale price, status = `pending`.
> 9. Clears the cart (items and promo code).

---

## 7. Quiz Endpoints

Quiz endpoints require authentication and the `student` role.

**Headers:** `Authorization: Bearer {token}`

---

### Get Quiz
`GET /api/v1/quizzes/{id}`

**Auth:** Required
**Role:** student

Returns quiz details with questions and options. The `is_correct` flag on options is **not** included in the response (to prevent cheating). The student must be enrolled in the course that the quiz belongs to.

**URL Parameters:**

| Param | Type    | Description |
|-------|---------|-------------|
| id    | integer | Quiz ID     |

**Response (200):**

```json
{
  "data": {
    "id": 1,
    "title": "Module 1 Quiz",
    "passing_score": 70,
    "time_limit_minutes": 15,
    "max_attempts": 3,
    "questions": [
      {
        "id": 1,
        "question_text": "What does HTML stand for?",
        "sort_order": 1,
        "options": [
          {
            "id": 1,
            "option_label": "A",
            "option_text": "Hyper Text Markup Language"
          },
          {
            "id": 2,
            "option_label": "B",
            "option_text": "High Tech Modern Language"
          },
          {
            "id": 3,
            "option_label": "C",
            "option_text": "Hyper Transfer Markup Language"
          },
          {
            "id": 4,
            "option_label": "D",
            "option_text": "Home Tool Markup Language"
          }
        ]
      }
    ]
  }
}
```

**Error Response (403):**

```json
{ "message": "You are not enrolled in this course." }
```

---

### Submit Quiz Attempt
`POST /api/v1/quizzes/{id}/attempt`

**Auth:** Required
**Role:** student

Submits answers for a quiz. The system grades each answer, calculates the score, and determines pass/fail. If passed and the quiz is linked to a lesson, the lesson is marked as complete and course progress is recalculated.

**URL Parameters:**

| Param | Type    | Description |
|-------|---------|-------------|
| id    | integer | Quiz ID     |

**Request Body:**

| Field              | Type   | Required | Description                                          |
|--------------------|--------|----------|------------------------------------------------------|
| answers            | object | Yes      | Map of `question_id` to selected option label        |
| answers.{question_id} | string | Yes   | The selected option label (e.g., "A", "B", "C", "D") |

**Example Request Body:**

```json
{
  "answers": {
    "1": "A",
    "2": "C",
    "3": "B"
  }
}
```

**Response (200):**

```json
{
  "data": {
    "score": 66.67,
    "passed": false,
    "attempt_number": 1,
    "total_questions": 3,
    "correct_count": 2,
    "answers": [
      {
        "question_id": 1,
        "question_text": "What does HTML stand for?",
        "selected_answer": "A",
        "correct_answer": "A",
        "is_correct": true,
        "explanation": "HTML stands for Hyper Text Markup Language."
      },
      {
        "question_id": 2,
        "question_text": "Which tag is used for paragraphs?",
        "selected_answer": "C",
        "correct_answer": "B",
        "is_correct": false,
        "explanation": "The <p> tag is used for paragraphs."
      }
    ]
  }
}
```

**Error Response (422):**

```json
{ "message": "You have reached the maximum number of attempts for this quiz." }
```

**Error Response (403):**

```json
{ "message": "You are not enrolled in this course." }
```

> **Business Logic:**
> - The student must be enrolled in the quiz's course.
> - The number of previous attempts is checked against `max_attempts`.
> - Each answer is graded by comparing the selected label against the correct option's label.
> - Score is calculated as `(correct_count / total_questions) * 100`.
> - If `score >= passing_score`, the attempt is marked as `passed`.
> - If passed and the quiz has an associated lesson: that lesson is marked as completed, progress is recalculated, and if all lessons + quizzes are done, a certificate is generated.

---

## 8. User Profile

Profile endpoints require authentication (any role).

**Headers:** `Authorization: Bearer {token}`

---

### Update Profile
`PUT /api/v1/user/profile`

**Auth:** Required

Updates the authenticated user's profile. If the user is an instructor, instructor-specific profile fields can also be updated.

**Request Body (Students):**

| Field | Type   | Required | Description           |
|-------|--------|----------|-----------------------|
| name  | string | No       | Full name (max 255)   |
| phone | string | No       | Phone number (max 20) |

**Request Body (Instructors - additional fields):**

| Field               | Type    | Required | Description                                                    |
|---------------------|---------|----------|----------------------------------------------------------------|
| bio                 | string  | No       | Instructor biography                                           |
| specialization      | string  | No       | Medical specialization                                         |
| years_of_experience | integer | No       | Years of experience                                            |
| qualifications      | array   | No       | List of qualifications, e.g. `["MBBS", "MD", "MRCP"]`         |
| education           | array   | No       | Education history, e.g. `[{"degree":"MBBS","institution":"Cairo University","year":2010}]` |
| expertise           | array   | No       | Areas of expertise, e.g. `["Internal Medicine", "Cardiology"]` |
| social_links        | object  | No       | Social media links, e.g. `{"linkedin":"...","twitter":"...","website":"..."}` |

**Response (200):**

```json
{
  "data": {
    "id": 1,
    "name": "John Doe Updated",
    "email": "john@example.com",
    "phone": "+1234567890",
    "avatar": "/storage/avatars/abc.jpg",
    "role": "student",
    "is_active": true,
    "created_at": "2026-01-01T10:00:00.000000Z"
  },
  "message": "Profile updated successfully."
}
```

---

### Update Password
`PUT /api/v1/user/password`

**Auth:** Required

**Request Body:**

| Field                 | Type   | Required | Description                    |
|-----------------------|--------|----------|--------------------------------|
| current_password      | string | Yes      | Must match current password    |
| password              | string | Yes      | New password (min 8 chars)     |
| password_confirmation | string | Yes      | Must match new password        |

**Response (200):**

```json
{
  "message": "Password updated successfully."
}
```

**Error Response (422):**

```json
{
  "message": "The current password is incorrect.",
  "errors": {
    "current_password": ["The current password is incorrect."]
  }
}
```

---

### Upload Avatar
`POST /api/v1/user/avatar`

**Auth:** Required
**Content-Type:** `multipart/form-data`

Uploads a new avatar image. If the user already has an avatar, the old file is deleted from storage.

**Request Body (form-data):**

| Field  | Type | Required | Description                                         |
|--------|------|----------|-----------------------------------------------------|
| avatar | file | Yes      | Image file (jpeg, png, jpg, gif, webp). Max 2MB.    |

**Response (200):**

```json
{
  "data": {
    "avatar": "/storage/avatars/abc123def.jpg"
  },
  "message": "Avatar uploaded successfully."
}
```

**Error Response (422):**

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "avatar": ["The avatar field must be an image.", "The avatar field must not be greater than 2048 kilobytes."]
  }
}
```

---

### Delete Avatar
`DELETE /api/v1/user/avatar`

**Auth:** Required

Removes the user's avatar from storage and sets the avatar field to `null`.

**Request Body:** None

**Response (200):**

```json
{
  "message": "Avatar removed successfully."
}
```

---

## Appendix

### Role-Based Access Summary

| Endpoint Prefix          | Required Role | Middleware              |
|--------------------------|---------------|-------------------------|
| `/api/v1/auth/*`         | None / Any    | Public or `auth:sanctum` |
| `/api/v1/courses`        | None          | Public                  |
| `/api/v1/categories`     | None          | Public                  |
| `/api/v1/instructors/*`  | None          | Public                  |
| `/api/v1/subscription-plans` | None      | Public                  |
| `/api/v1/contact`        | None          | Public                  |
| `/api/v1/cart/*`         | Any auth user | `auth:sanctum`          |
| `/api/v1/checkout`       | Any auth user | `auth:sanctum`          |
| `/api/v1/user/*`         | Any auth user | `auth:sanctum`          |
| `/api/v1/student/*`      | student       | `auth:sanctum`, `role:student` |
| `/api/v1/quizzes/*`      | student       | `auth:sanctum`, `role:student` |
| `/api/v1/instructor/*`   | instructor    | `auth:sanctum`, `role:instructor` |

### Platform Fee Structure

- The platform charges a **20% fee** on each course sale.
- Instructor receives **80%** (`net_amount`) of the sale price.
- Example: Course sold for 100.00 => platform_fee = 20.00, net_amount = 80.00.

### Certificate Generation Rules

A certificate is automatically generated when **both** conditions are met:
1. All lessons in the course are marked as completed (progress = 100%).
2. All quizzes in the course have at least one passing attempt by the student.

Certificates are assigned:
- A unique `certificate_number` in format `CERT-XXXXXXXX` (8 random alphanumeric characters).
- An `issued_at` timestamp of the current date/time.
- An `expires_at` date set to 2 years after issuance.

### Order Number Format

Order numbers follow the pattern `ORD-XXXXXXXX` (8 random alphanumeric uppercase characters).

### Transaction Number Format

Transaction numbers follow the pattern `TXN-XXXXX` (5 random alphanumeric uppercase characters).
