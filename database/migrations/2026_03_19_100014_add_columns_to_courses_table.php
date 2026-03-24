<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('preview_video_url', 500)->nullable()->after('image');
            $table->unsignedInteger('total_duration_minutes')->default(0)->after('tags');
            $table->unsignedInteger('students_count_cached')->default(0)->after('total_duration_minutes');
            $table->decimal('average_rating_cached', 3, 2)->default(0.00)->after('students_count_cached');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['preview_video_url', 'total_duration_minutes', 'students_count_cached', 'average_rating_cached']);
        });
    }
};
