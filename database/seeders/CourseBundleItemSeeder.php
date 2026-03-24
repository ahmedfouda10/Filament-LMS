<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseBundleItem;
use Illuminate\Database\Seeder;

class CourseBundleItemSeeder extends Seeder
{
    public function run(): void
    {
        $cardioBundle = Course::where('title', 'like', '%Cardiology Bundle%')->first();
        $ecg = Course::where('title', 'like', '%ECG%')->first();
        $acls = Course::where('title', 'like', '%ACLS%')->first();

        if ($cardioBundle && $ecg) CourseBundleItem::firstOrCreate(['bundle_id' => $cardioBundle->id, 'course_id' => $ecg->id], ['sort_order' => 0]);
        if ($cardioBundle && $acls) CourseBundleItem::firstOrCreate(['bundle_id' => $cardioBundle->id, 'course_id' => $acls->id], ['sort_order' => 1]);

        $surgeryBundle = Course::where('title', 'like', '%Surgery%Bundle%')->first();
        $skills = Course::where('title', 'like', '%Surgical Skills%')->first();
        $general = Course::where('title', 'like', '%General Surgery%')->first();

        if ($surgeryBundle && $skills) CourseBundleItem::firstOrCreate(['bundle_id' => $surgeryBundle->id, 'course_id' => $skills->id], ['sort_order' => 0]);
        if ($surgeryBundle && $general) CourseBundleItem::firstOrCreate(['bundle_id' => $surgeryBundle->id, 'course_id' => $general->id], ['sort_order' => 1]);
    }
}
