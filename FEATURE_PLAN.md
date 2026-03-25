# SPC Online Academy - Feature Plan

> This file tracks missing features discovered from reviewing the frontend.
> Each feature has: what's needed, the API design, and implementation notes.
> Status: `[ ]` = Not started, `[x]` = Done

---

## 1. Public Stats API

**Source:** Homepage hero section shows stats (15,000+ Active Students, 120+ Courses, etc.)

**Problem:** Stats are only available in the admin dashboard widget. No public API.

**Solution:**

```
GET /api/v1/stats
```

**Response:**
```json
{
  "data": {
    "active_students": 15000,
    "total_courses": 120,
    "total_instructors": 9500,
    "average_rating": 4.8
  }
}
```

**Implementation:**
- [ ] Create `StatsController.php` in `Api/V1/`
- [ ] Query: `User::where('role','student')->count()`, `Course::published()->count()`, `User::where('role','instructor')->count()`, `Review::avg('rating')`
- [ ] Add route: `Route::get('/stats', [StatsController::class, 'index']);` (public, no auth)
- [ ] Cache results for 1 hour (`Cache::remember('public_stats', 3600, ...)`)

---

## 2. Hero Video URL Setting

**Source:** Homepage has "Watch Free Clinical Preview" video button

**Problem:** No setting to store the hero video URL

**Solution:**
- [ ] Add setting key: `hero_video_url` to SettingsSeeder
- [ ] Already returned via `GET /api/v1/settings`
- [ ] Add to Filament Settings page under "General Settings" section

---

## 3. Announcements / Banner System

**Source:** Homepage shows "New Clinical Study Cases Available" green badge

**Problem:** No way for admin to set/change this announcement

**Solution Option A (Simple - Settings based):**
- [ ] Add setting keys: `announcement_text`, `announcement_enabled`, `announcement_color`
- [ ] Returned via `GET /api/v1/settings`
- [ ] Add to Filament Settings page under new "Announcements" section

**Solution Option B (Advanced - Full CRUD):**
- [ ] Create `announcements` table (id, title, type, is_active, starts_at, ends_at, timestamps)
- [ ] Create Announcement model + controller
- [ ] `GET /api/v1/announcements` (public, returns active announcements)
- [ ] Filament resource for CRUD

**Recommendation:** Option A is sufficient for now.

---

## 4. About Page Content

**Source:** About page shows title, description, mission, vision, and core values

**Problem:** No API to serve About page content

**Solution:**
- [ ] Add setting keys to seeder + Filament Settings:
  - `about_title` → "About SPC Online Academy"
  - `about_description` → "Empowering the next generation..."
  - `about_mission` → "To bridge the gap between theoretical..."
  - `about_vision` → "To become the leading digital standard..."
  - `about_values` → JSON: `[{"title":"Evidence-Based","description":"All our courses...","icon":"book"},{"title":"Community Driven","description":"We foster...","icon":"users"},{"title":"Practical Focus","description":"Our curriculum...","icon":"target"}]`
- [ ] Already returned via `GET /api/v1/settings` (public endpoint)
- [ ] Add to Filament Settings page under new "About Page" section with RichEditor for mission/vision and Repeater for core values

---

## 5. Bundle Courses Count in Listing

**Source:** Bundles section shows "5 Courses", "7 Courses" badge on each bundle card

**Problem:** `GET /courses?is_bundle=true` doesn't return `bundled_courses_count`. The `bundledCourses` relation is only loaded in `GET /bundles/{slug}` (detail page), not in the listing.

**Solution:**
- [ ] Update `CourseController@index` to add `withCount('bundleItems')` when `is_bundle` filter is applied
- [ ] Update `CourseResource` to include `bundled_courses_count` when available
- [ ] OR add a dedicated `GET /api/v1/bundles` endpoint that returns bundles with courses count

**API Change:**
```json
{
  "id": 13,
  "title": "Complete Cardiology Bundle",
  "is_bundle": true,
  "bundled_courses_count": 3,
  "...": "..."
}
```

---

## 6. Bundle Badge System

**Source:** Bundle cards show badges like "Most Popular", "Hot Bundle", "Save 35%"

**Problem:** No field for custom badges on courses/bundles

**Solution:**
- [ ] Add `badge_text` (string, nullable) and `badge_color` (string, nullable) columns to `courses` table
- [ ] Migration: `$table->string('badge_text', 50)->nullable()` and `$table->string('badge_color', 20)->nullable()`
- [ ] Add to Course model `$fillable`
- [ ] Add to Filament CourseResource form (text input + color select)
- [ ] "Save X%" can be calculated by frontend: `round((original_price - price) / original_price * 100)`

**API Change:**
```json
{
  "id": 13,
  "title": "Complete Cardiology Bundle",
  "badge_text": "Most Popular",
  "badge_color": "blue",
  "...": "..."
}
```

**Badge colors mapping:**
- `blue` = "Most Popular"
- `green` = "Save X%"
- `orange` = "Hot Bundle"
- `purple` = category name

---

## 7. Lessons Count in Course Listing

**Source:** Featured courses show "35 Lessons", "22 Lessons" on each card

**Problem:** `GET /courses/featured` doesn't return `lessons_count`. The lessons are nested inside modules and only loaded in course detail.

**Solution:**
- [ ] Add `lessons_count` to `CourseResource` — computed from `modules.lessons` count
- [ ] In `CourseController@index` and `@featured`, use: `withCount(['modules as lessons_count' => function($q) { ... }])` or a subquery
- [ ] Simplest: add a `getTotalLessonsAttribute()` on Course model that returns `$this->modules()->withCount('lessons')->get()->sum('lessons_count')`

**API Change:**
```json
{
  "id": 1,
  "title": "ECG Interpretation Masterclass",
  "lessons_count": 35,
  "...": "..."
}
```

---

## 8. List All Instructors API

**Source:** Instructors section has "View All Doctors" link

**Problem:** Only `GET /instructors/{id}` (single) exists. No endpoint to list all instructors.

**Solution:**

```
GET /api/v1/instructors
```

**Response:**
```json
{
  "data": [
    {
      "id": 2,
      "name": "Dr. Ahmed Hassan",
      "avatar": "/storage/avatars/ahmed.jpg",
      "specialization": "Internal Medicine",
      "average_rating": 4.8,
      "total_students": 5200,
      "courses_count": 6
    }
  ]
}
```

**Implementation:**
- [ ] Add `index()` method to `InstructorController`
- [ ] Query: `User::where('role', 'instructor')->where('is_active', true)->with('instructorProfile')->get()`
- [ ] Add route: `Route::get('/instructors', [InstructorController::class, 'index']);`
- [ ] Include computed: `courses_count`, `total_students`, `average_rating`, `specialization` from profile

---

## 9. Homepage Testimonials API

**Source:** "What Our Students Say" section with top reviews across all courses

**Problem:** Reviews API (`GET /student/reviews`) is per-student. No public endpoint for best reviews across the platform.

**Solution:**

```
GET /api/v1/testimonials?limit=8
```

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "comment": "The EKG course was a game changer...",
      "rating": 5,
      "user": {
        "name": "Dr. Mona Ibrahim",
        "avatar": "/storage/avatars/mona.jpg",
        "title": "Pediatric Resident"
      },
      "course": {
        "title": "ECG Interpretation Masterclass"
      }
    }
  ]
}
```

**Implementation:**
- [ ] Create `TestimonialController.php` in `Api/V1/`
- [ ] Query: `Review::where('is_approved', true)->where('rating', '>=', 4)->with('user:id,name,avatar', 'course:id,title')->inRandomOrder()->limit(8)->get()`
- [ ] Add route: `Route::get('/testimonials', [TestimonialController::class, 'index']);` (public)
- [ ] **Also need:** `title` or `position` field on User model (e.g. "Pediatric Resident", "Surgical Assistant")

---

## 10. User Title/Position Field

**Source:** Testimonials show user titles like "PEDIATRIC RESIDENT", "SURGICAL ASSISTANT"

**Problem:** User model has no `title` or `position` field

**Solution:**
- [ ] Migration: add `title` (string, 255, nullable) to `users` table
- [ ] Add to User model `$fillable`
- [ ] Add to `UpdateProfileRequest` validation
- [ ] Add to `ProfileController@update`
- [ ] Add to `UserResource` response
- [ ] Add to Filament `UserResource` form
- [ ] Add to `RegisterRequest` (optional field)

---

## 11. Static Pages + FAQ System

**Source:** Footer links: FAQ, Terms of Service, Privacy Policy

**Problem:** No system for static/legal pages OR structured FAQ

**Solution (2 parts):**

**Part A - FAQ (structured):**
- [ ] Create `faqs` table: `id, category (string), question (string), answer (text), sort_order (int), is_active (bool), timestamps`
- [ ] Model: `Faq` with `scopeActive()`, grouped by category
- [ ] `GET /api/v1/faqs` → returns FAQs grouped by category
- [ ] Filament: `FaqResource` with CRUD (category dropdown, question, answer RichEditor, sort, toggle active)
- [ ] Seeder: 6-8 FAQs across 3 categories (Subscriptions, Courses & Content, Certificates)

**Part B - Legal Pages (simple):**
- [ ] Add setting keys: `page_terms` (HTML), `page_privacy` (HTML)
- [ ] `GET /api/v1/pages/{slug}` → returns HTML content from settings
- [ ] Add to Filament Settings under "Legal Pages" section with RichEditor

---

## 12. Bundle Detail - Reviews Count

**Source:** Bundle header shows "(840 ratings)" next to the rating stars

**Problem:** `GET /bundles/{slug}` returns `average_rating` but not `reviews_count`

**Solution:**
- [ ] Update `BundleController@show` to include `reviews_count`
- [ ] For a bundle: aggregate reviews from all bundled courses, OR count reviews on the bundle course itself

**API Change:**
```json
{
  "average_rating": 4.9,
  "reviews_count": 840,
  "...": "..."
}
```

---

## 13. Bundle Detail - Multiple Instructors

**Source:** Bundle shows "Taught by Multiple Instructors" because bundled courses have different instructors

**Problem:** `GET /bundles/{slug}` only returns one `instructor`. Bundles may have courses from multiple instructors.

**Solution:**
- [ ] Update `BundleController@show` to collect unique instructors from all bundled courses
- [ ] Return `instructors` array instead of single `instructor`

**API Change:**
```json
{
  "instructors": [
    { "id": 2, "name": "Dr. Ahmed Hassan", "avatar": "..." },
    { "id": 3, "name": "Dr. Mona Ibrahim", "avatar": "..." }
  ],
  "...": "..."
}
```

---

## 14. Bundle Detail - Bundled Courses with Lessons Count & Duration

**Source:** "Courses Included" section shows each course with "45 lectures • 12h 30m"

**Problem:** Bundled courses in `GET /bundles/{slug}` don't include `lessons_count` or `total_duration`

**Solution:**
- [ ] Update `BundleController@show` → load bundled courses with `withCount` for lessons and computed duration
- [ ] Each bundled course needs: `title`, `lessons_count`, `total_duration`

**API Change:**
```json
{
  "courses": [
    {
      "id": 1,
      "title": "Advanced Clinical Study Cases: Internal Medicine",
      "lessons_count": 45,
      "total_duration": 750,
      "...": "..."
    }
  ]
}
```

---

## 15. Bundle Detail - Reviews List

**Source:** Bottom of bundle page shows reviews with user name, rating, comment, date

**Problem:** `GET /bundles/{slug}` doesn't return reviews at all

**Solution:**
- [ ] Update `BundleController@show` to include `reviews` (latest 10, with user info)
- [ ] OR create separate endpoint: `GET /bundles/{slug}/reviews?page=1`

**API Change (inline):**
```json
{
  "reviews": [
    {
      "id": 1,
      "user": { "name": "Dr. Ahmed", "avatar": "..." },
      "rating": 5,
      "comment": "Incredible value...",
      "created_at": "2026-03-10T10:00:00Z"
    }
  ]
}
```

---

## 16. Category Detail API

**Source:** Category page (`/courses/category/{slug}`) shows category info, stats, and featured videos

**Problem:** No `GET /categories/{slug}` endpoint. Only `GET /categories` (list all) exists.

**Solution:**

```
GET /api/v1/categories/{slug}
```

**Response:**
```json
{
  "data": {
    "id": 1,
    "name": "Exam Preparation",
    "slug": "exam-preparation",
    "icon": "clipboard-check",
    "description": "Structured video courses designed for medical board exam success...",
    "courses_count": 3,
    "total_students": 3550,
    "featured_videos_count": 4,
    "courses": [
      {
        "id": 1,
        "title": "Introduction to Exam Prep",
        "slug": "intro-exam-prep",
        "image": "...",
        "price": 1500,
        "instructor": { "id": 2, "name": "Dr. Ahmed", "avatar": "..." }
      }
    ]
  }
}
```

**Implementation:**
- [ ] Add `show()` method to `CategoryController`
- [ ] Route: `Route::get('/categories/{slug}', [CategoryController::class, 'show']);`
- [ ] Load category with courses (published), compute `total_students` from enrollments
- [ ] Include `courses` list with instructor info

---

## 17. Category Featured Videos / Free Lessons API

**Source:** Category page shows "Featured Videos" section with free video lessons from that category's courses

**Problem:** No endpoint to get free/featured video lessons per category

**Solution:**

```
GET /api/v1/categories/{slug}/videos?search=keyword&sort_by=most_viewed&per_page=8
```

**Response:**
```json
{
  "data": [
    {
      "id": 10,
      "title": "Introduction to Exam Preparation",
      "type": "video",
      "duration_minutes": 32,
      "video_url": "https://...",
      "thumbnail": "/storage/thumbnails/lesson-10.jpg",
      "views_count": 10200,
      "is_free": true,
      "instructor": {
        "id": 2,
        "name": "Dr. Ahmed Hassan",
        "avatar": "..."
      },
      "course": {
        "id": 1,
        "title": "Exam Prep Course"
      }
    }
  ]
}
```

**Implementation:**
- [ ] Create `CategoryVideoController.php` or add to `CategoryController`
- [ ] Route: `Route::get('/categories/{slug}/videos', [CategoryController::class, 'videos']);`
- [ ] Query: lessons where type='video' AND is_free=true AND course.category.slug = {slug} AND course.is_published = true
- [ ] Support: `search` (title), `sort_by` (most_viewed, newest, duration), pagination

---

## 18. Lesson Views Tracking

**Source:** Category videos show view counts (10,200 / 7,800 etc.)

**Problem:** No view tracking for individual lessons

**Solution:**
- [ ] Add `views_count` column to `lessons` table: `$table->unsignedInteger('views_count')->default(0);`
- [ ] Create middleware or increment in `StudentCourseController@player` when a lesson is accessed
- [ ] OR create `lesson_views` table for detailed tracking (user_id, lesson_id, viewed_at) and use count

**Simple approach:**
```php
// In StudentCourseController@player or a dedicated endpoint:
Lesson::where('id', $lessonId)->increment('views_count');
```

---

## 19. Lesson Thumbnails

**Source:** Category videos show thumbnail images for each video lesson

**Problem:** Lessons have `video_url` but no `thumbnail` field

**Solution:**
- [ ] Add `thumbnail` (string, 500, nullable) to `lessons` table
- [ ] Add to Lesson model `$fillable`
- [ ] Add to `StoreLessonRequest` validation
- [ ] Add to Filament Lesson management (file upload or URL input)
- [ ] Alternative: auto-generate from video URL (if YouTube/Vimeo, extract thumbnail)

---

## 20. Subscribe to Category

**Source:** Category page has "Subscribe to Category" button

**Problem:** No category subscription system. Current subscriptions are platform-wide plans.

**Solution Option A (Notification-based - simple):**
- [ ] Create `category_subscriptions` table (user_id, category_id, created_at)
- [ ] When new course added to category → notify subscribed users
- [ ] `POST /api/v1/categories/{slug}/subscribe` (auth required)
- [ ] `DELETE /api/v1/categories/{slug}/unsubscribe`
- [ ] `GET /api/v1/categories` response includes `is_subscribed: true/false` for auth users

**Solution Option B (Just a follow/interest system):**
- [ ] Same as A but without notification triggers initially
- [ ] Use it to personalize homepage recommendations

**Recommendation:** Option A

---

## 21. Course Detail - Module Stats (lessons_count + duration per module)

**Source:** Course content accordion shows "5 lectures • 2h 15m" per module

**Problem:** `GET /courses/{slug}` returns modules with lessons but doesn't include per-module aggregated stats

**Solution:**
- [ ] Update `CourseController@show` or `CourseDetailResource` to include `lessons_count` and `total_duration` per module
- [ ] In Module loading: `modules()->withCount('lessons')` and compute duration sum from lessons

**API Change (in modules array):**
```json
{
  "modules": [
    {
      "id": 1,
      "title": "Cardiovascular Emergencies",
      "sort_order": 1,
      "lessons_count": 5,
      "total_duration_minutes": 135,
      "lessons": [...]
    }
  ]
}
```

---

## 22. Course Detail - Quiz Questions Count

**Source:** Quiz lessons show "10 Qs" instead of duration

**Problem:** Quiz-type lessons don't return the number of questions. The quiz object inside lesson only has `id`, `title`, `passing_score`, `max_attempts`, `time_limit`.

**Solution:**
- [ ] When loading lessons in course detail, for quiz-type lessons include `quiz.questions_count`
- [ ] Use `withCount` on quiz relation: `quiz()->withCount('questions')`

**API Change (in lesson object):**
```json
{
  "id": 5,
  "title": "Cardio Assessment Quiz",
  "type": "quiz",
  "quiz": {
    "id": 1,
    "title": "Cardio Assessment Quiz",
    "questions_count": 10,
    "passing_score": 70,
    "max_attempts": 3,
    "time_limit_minutes": 15
  }
}
```

---

## 23. Course Detail - Preview Video URL in Response

**Source:** Sidebar shows video preview with play button and "FREE PREVIEW" badge

**Problem:** `preview_video_url` column was added in Phase 2 migration but is NOT returned in `CourseDetailResource`

**Solution:**
- [ ] Add `preview_video_url` to `CourseDetailResource` response
- [ ] Add to `CourseResource` as well (for listing pages that show preview)

---

## 24. Instructor Profile - Full Course Cards

**Source:** Instructor page shows "My Courses" with image, title, students, rating, price + sorting

**Problem:** `GET /instructors/{id}` loads courses but `InstructorResource` doesn't return full course details (image, price, students_count, rating per course). Currently only returns aggregate stats.

**Solution:**
- [ ] Update `InstructorController@show` to load courses with full details
- [ ] Update `InstructorResource` to include `courses` array with: `id, title, slug, image, price, students_count, average_rating`
- [ ] Support optional `?sort_by=popular|newest|highest_rated` query param

**API Change:**
```json
{
  "data": {
    "id": 2,
    "name": "Dr. Ahmed Hassan",
    "courses": [
      {
        "id": 1,
        "title": "Advanced Clinical Study Cases",
        "slug": "advanced-clinical-study-cases",
        "image": "/storage/courses/clinical.jpg",
        "price": 1200,
        "students_count": 1540,
        "average_rating": 4.9
      }
    ],
    "...existing fields..."
  }
}
```

---

## 25. Instructor Profile - Featured Reviews

**Source:** Bottom of instructor page shows "Featured Reviews" with reviews from all their courses

**Problem:** `GET /instructors/{id}` doesn't return any reviews

**Solution:**
- [ ] Update `InstructorController@show` to load recent reviews across all instructor's courses
- [ ] Include: user name, course title, rating, comment, date

**API Change:**
```json
{
  "data": {
    "reviews": [
      {
        "id": 1,
        "user": { "name": "Dr. Khaled", "avatar": null },
        "course": { "title": "Advanced Clinical Cases" },
        "rating": 5,
        "comment": "Dr. Ahmed breaks down complex topics effortlessly...",
        "created_at": "2026-03-05T10:00:00Z"
      }
    ],
    "...existing fields..."
  }
}
```

**Implementation:**
- [ ] In `InstructorController@show`: `Review::whereIn('course_id', $instructor->courses->pluck('id'))->with('user:id,name,avatar', 'course:id,title')->where('is_approved', true)->latest()->limit(10)->get()`

---

## 26. Courses Filter - Free/Paid

**Source:** Browse courses sidebar has "Paid Courses" / "Free Courses" radio buttons

**Problem:** `GET /courses` supports `min_price`/`max_price` but no simple `price_type` param

**Solution:**
- [ ] Add `?price_type=free|paid` query param to `CourseController@index`
- [ ] `free` → `where('price', 0)` or `where('price', '<=', 0)`
- [ ] `paid` → `where('price', '>', 0)`

Simple addition - 3 lines in `CourseController@index`.

---

## Tracking

| # | Feature | Priority | Status |
|---|---------|----------|--------|
| 1 | Public Stats API | High | [ ] |
| 2 | Hero Video URL | Medium | [ ] |
| 3 | Announcements | Medium | [ ] |
| 4 | About Page Content | Low | [ ] |
| 5 | Bundle Courses Count in Listing | High | [ ] |
| 6 | Bundle Badge System | Medium | [ ] |
| 7 | Lessons Count in Course Listing | High | [ ] |
| 8 | List All Instructors API | High | [ ] |
| 9 | Homepage Testimonials API | High | [ ] |
| 10 | User Title/Position Field | Medium | [ ] |
| 11 | Static Pages (FAQ, Terms, Privacy) | Medium | [ ] |
| 12 | Bundle Detail - Reviews Count | High | [ ] |
| 13 | Bundle Detail - Multiple Instructors | Medium | [ ] |
| 14 | Bundle Detail - Courses with Lessons/Duration | High | [ ] |
| 15 | Bundle Detail - Reviews List | High | [ ] |
| 16 | Category Detail API | **Critical** | [ ] |
| 17 | Category Featured Videos API | **Critical** | [ ] |
| 18 | Lesson Views Tracking | High | [ ] |
| 19 | Lesson Thumbnails | Medium | [ ] |
| 20 | Subscribe to Category | Medium | [ ] |
| 21 | Module Stats (lessons_count + duration) | High | [ ] |
| 22 | Quiz Questions Count in Course Detail | Medium | [ ] |
| 23 | Preview Video URL in Response | High | [ ] |
| 24 | Instructor - Full Course Cards | High | [ ] |
| 25 | Instructor - Featured Reviews | High | [ ] |
| 26 | Courses Filter - Free/Paid | Low | [ ] |
| 27 | Student Dashboard - Weekly Lessons Count | Low | [ ] |
| 28 | Student Dashboard - Instructor Avatar in Continue Learning | Low | [ ] |
| 29 | Subscription Invoice PDF | Medium | [ ] |
| 30 | Instructor Dashboard - Revenue Chart Data | High | [ ] |
| 31 | Instructor Dashboard - Students Growth % | Medium | [ ] |
| 32 | Instructor Courses - Published/Draft filter + search | High | [ ] |
| 33 | Instructor Courses - Revenue per course in listing | High | [ ] |
| 34 | Instructor Course Detail - Revenue + Completion Rate + Reviews Count | High | [ ] |
| 35 | Instructor Course Detail - Enrolled Students list | Medium | [ ] |
| 36 | Instructor Transactions CSV Export | Medium | [ ] |

---

## Final Summary (All Pages Reviewed)

### Pages Coverage

| Page | Status | Missing Features |
|------|--------|-----------------|
| Homepage - Hero | 90% | #1 Stats API, #2 Hero Video, #3 Announcements |
| Homepage - Bundles | 85% | #5 Bundle count, #6 Badges |
| Homepage - Categories | 100% | - |
| Homepage - Featured Courses | 85% | #7 Lessons count |
| Homepage - Instructors | 85% | #8 List all instructors |
| Homepage - Testimonials | 0% | #9 Testimonials API, #10 User title |
| Homepage - Footer | 90% | #11 FAQ/Terms/Privacy |
| Browse Courses | 85% | #5, #7, #26 Free/Paid filter |
| Bundles Listing | 95% | #5 Bundle count |
| Bundle Detail | 70% | #12 Reviews count, #13 Multi-instructors, #14 Courses stats, #15 Reviews |
| Category Page | 30% | #16 Category detail, #17 Videos API, #18 Views, #19 Thumbnails, #20 Subscribe |
| Course Detail | 90% | #21 Module stats, #22 Quiz count, #23 Preview URL |
| Instructor Profile | 70% | #10 Title, #24 Course cards, #25 Reviews |
| Subscriptions | 100% | - |
| About | 0% | #4 About content |
| Contact | 98% | - |
| FAQ | 0% | #11 FAQ system |
| Terms / Privacy | 0% | #11 Legal pages |
| Student Dashboard | 90% | #27 Weekly count, #28 Avatar |
| Student My Courses | 90% | - |
| Student Certificates | 90% | - |
| Student Subscriptions | 70% | #29 Invoice PDF |
| Student Settings | 95% | #10 Title field |
| Instructor Dashboard | 80% | #30 Revenue chart, #31 Growth % |
| Instructor Courses | 75% | #32 Filters, #33 Revenue/course |
| Instructor Course Detail | 65% | #34 Stats, #35 Students list |
| Instructor Revenue | 92% | #36 CSV Export |
| Instructor Settings | 95% | #10 Title field |

### Implementation Priority

**Critical (Endpoint doesn't exist - page broken):**
| # | Feature | Type | Effort |
|---|---------|------|--------|
| 16 | Category Detail API | New endpoint | Medium |
| 17 | Category Featured Videos API | New endpoint | Medium |

**High (Data missing - page incomplete):**
| # | Feature | Type | Effort |
|---|---------|------|--------|
| 1 | Public Stats API | New endpoint | Small |
| 5 | Bundle Courses Count | Modify existing | Small |
| 7 | Lessons Count in Listings | Modify existing | Small |
| 8 | List All Instructors | New endpoint | Small |
| 9 | Testimonials API | New endpoint | Small |
| 12 | Bundle Reviews Count | Modify existing | Small |
| 14 | Bundle Courses with Stats | Modify existing | Small |
| 15 | Bundle Reviews List | Modify existing | Small |
| 21 | Module Stats | Modify existing | Small |
| 23 | Preview Video URL | Modify existing | 1 line |
| 24 | Instructor Course Cards | Modify existing | Small |
| 25 | Instructor Reviews | Modify existing | Small |
| 30 | Revenue Chart Data | Modify existing | Medium |
| 32 | Instructor Course Filters | Modify existing | Small |
| 33 | Revenue per Course | Modify existing | Medium |
| 34 | Course Detail Stats | Modify existing | Medium |

**Medium (Feature gap - nice to have):**
| # | Feature | Type | Effort |
|---|---------|------|--------|
| 2 | Hero Video URL | Add setting | Tiny |
| 3 | Announcements | Add settings | Small |
| 4 | About Page Content | Add settings | Small |
| 6 | Bundle Badge System | Migration + field | Small |
| 10 | User Title Field | Migration + field | Small |
| 11 | FAQ + Legal Pages | New table + endpoints | Medium |
| 13 | Bundle Multi-Instructors | Modify existing | Small |
| 18 | Lesson Views Tracking | Migration + middleware | Medium |
| 19 | Lesson Thumbnails | Migration + field | Small |
| 20 | Subscribe to Category | New table + endpoints | Medium |
| 22 | Quiz Questions Count | Modify existing | Small |
| 29 | Subscription Invoice PDF | New endpoint | Medium |
| 31 | Students Growth % | Modify existing | Small |
| 35 | Enrolled Students List | New/modify endpoint | Small |
| 36 | Transactions CSV Export | New endpoint | Small |

**Low:**
| # | Feature | Type | Effort |
|---|---------|------|--------|
| 26 | Free/Paid Filter | Modify existing | Tiny |
| 27 | Weekly Lessons Count | Modify existing | Tiny |
| 28 | Instructor Avatar | Modify existing | Tiny |

### Work Estimate

| Category | Count | New Endpoints | Modifications | New Tables |
|----------|-------|---------------|---------------|------------|
| Critical | 2 | 2 | 0 | 0 |
| High | 16 | 4 | 12 | 0 |
| Medium | 15 | 4 | 6 | 2 (faqs, category_subscriptions) |
| Low | 3 | 0 | 3 | 0 |
| **Total** | **36** | **10** | **21** | **2** |
