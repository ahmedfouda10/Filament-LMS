<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General
            ['key' => 'site_name', 'value' => 'SPC Online Academy'],
            ['key' => 'logo', 'value' => '/images/logo-spc.png'],
            ['key' => 'site_description', 'value' => 'Empowering Medical Professionals with high-quality clinical courses'],
            ['key' => 'contact_phone', 'value' => '+20 100 123 4567'],
            ['key' => 'contact_email', 'value' => 'support@spc-academy.com'],
            ['key' => 'address', 'value' => 'Cairo, Egypt'],
            ['key' => 'working_hours', 'value' => 'Sun - Thu: 9:00 AM - 5:00 PM'],

            // Appearance
            ['key' => 'primary_color', 'value' => '#236bba'],
            ['key' => 'secondary_color', 'value' => '#0f172a'],

            // Social Media
            ['key' => 'facebook_url', 'value' => 'https://facebook.com/spc-academy'],
            ['key' => 'twitter_url', 'value' => 'https://twitter.com/spc_academy'],
            ['key' => 'instagram_url', 'value' => 'https://instagram.com/spc_academy'],
            ['key' => 'linkedin_url', 'value' => 'https://linkedin.com/company/spc-academy'],
            ['key' => 'youtube_url', 'value' => 'https://youtube.com/@spc-academy'],

            // SEO
            ['key' => 'meta_title', 'value' => 'SPC Online Academy - Medical Education Platform'],
            ['key' => 'meta_description', 'value' => 'Learn from expert doctors through clinical case studies, video courses, and interactive quizzes.'],
            ['key' => 'meta_keywords', 'value' => 'medical education, clinical cases, MBBS, online courses, Egypt'],

            // Business
            ['key' => 'currency', 'value' => 'EGP'],
            ['key' => 'platform_fee_percentage', 'value' => '20'],
            ['key' => 'certificate_validity_years', 'value' => '2'],

            // Payment Gateway (Paymob)
            ['key' => 'paymob_api_key', 'value' => ''],
            ['key' => 'paymob_integration_id', 'value' => ''],
            ['key' => 'paymob_iframe_id', 'value' => ''],

            // Maintenance
            ['key' => 'maintenance_mode', 'value' => '0'],
            ['key' => 'maintenance_message', 'value' => 'We are currently performing scheduled maintenance. Please check back soon.'],

            // Phase 3 - System Limits
            ['key' => 'max_offline_downloads', 'value' => '5'],
            ['key' => 'download_token_hours', 'value' => '24'],
            ['key' => 'max_devices_per_user', 'value' => '3'],
            ['key' => 'installment_enabled', 'value' => 'true'],
            ['key' => 'installment_providers', 'value' => 'valu,sympl'],
            ['key' => 'messaging_enabled', 'value' => 'true'],

            // Phase 4 - Social Login
            ['key' => 'google_client_id', 'value' => ''],
            ['key' => 'google_client_secret', 'value' => ''],
            ['key' => 'facebook_client_id', 'value' => ''],
            ['key' => 'facebook_client_secret', 'value' => ''],

            // Feature Flags
            ['key' => 'feature_social_login', 'value' => 'false'],
            ['key' => 'feature_live_chat', 'value' => 'false'],
            ['key' => 'feature_push_notifications', 'value' => 'false'],
            ['key' => 'feature_offline_downloads', 'value' => 'true'],
            ['key' => 'feature_messaging', 'value' => 'true'],

            // Hero (#2)
            ['key' => 'hero_video_url', 'value' => ''],

            // Announcements (#3)
            ['key' => 'announcement_text', 'value' => 'New Clinical Study Cases Available'],
            ['key' => 'announcement_enabled', 'value' => 'true'],
            ['key' => 'announcement_color', 'value' => 'green'],

            // About Page (#4)
            ['key' => 'about_title', 'value' => 'About SPC Online Academy'],
            ['key' => 'about_description', 'value' => 'Empowering the next generation of medical professionals with accessible, high-quality clinical education.'],
            ['key' => 'about_mission', 'value' => 'To bridge the gap between theoretical medical knowledge and practical clinical application by providing expert-led, interactive study cases and comprehensive modules accessible to students and professionals worldwide.'],
            ['key' => 'about_vision', 'value' => 'To become the leading digital standard for continuing medical education, recognized globally for excellence in curriculum design, expert instructors, and measurable improvements in patient care outcomes.'],
            ['key' => 'about_values', 'value' => '[{"title":"Evidence-Based","description":"All our courses are rooted in the latest clinical guidelines and peer-reviewed research.","icon":"book"},{"title":"Community Driven","description":"We foster a supportive environment where medical professionals learn from each other.","icon":"users"},{"title":"Practical Focus","description":"Our curriculum emphasizes real-world diagnostic reasoning over rote memorization.","icon":"target"}]'],

            // Legal Pages (#11)
            ['key' => 'page_terms', 'value' => '<h2>1. Acceptance of Terms</h2><p>By accessing and using SPC Online Academy (the "Platform"), you agree to be bound by these Terms of Service. If you do not agree with any part of these terms, you must not use our services.</p><h2>2. Academic Integrity and Use</h2><p>Our courses and clinical cases are designed for educational purposes only. They do not constitute formal medical advice and should not replace clinical judgment in practice.</p><ul><li>You must provide accurate information when registering.</li><li>Account sharing is strictly prohibited and monitored via device tracking.</li><li>Course materials (videos, PDFs) are copyrighted and cannot be redistributed.</li></ul><h2>3. Payments and Subscriptions</h2><p>All payments are processed securely via our third-party payment gateways (Paymob). Subscriptions will automatically renew unless canceled at least 24 hours before the end of the current billing cycle.</p><h2>4. Refund Policy</h2><p>We offer a 30-day money-back guarantee for individual course purchases if less than 20% of the course content has been viewed. Subscription fees are non-refundable once the billing cycle begins.</p><h2>5. Account Termination</h2><p>SPC Online Academy reserves the right to suspend or terminate accounts that violate these terms, including instances of piracy, abusive behavior towards instructors, or payment fraud.</p><h2>6. Limitation of Liability</h2><p>SPC Online Academy shall not be liable for any indirect, incidental, or consequential damages arising from the use of our Platform. Our total liability is limited to the amount paid by the user in the preceding 12 months.</p><h2>7. Changes to Terms</h2><p>We reserve the right to update these terms at any time. Users will be notified of significant changes via email. Continued use of the Platform after changes constitutes acceptance.</p><h2>8. Contact</h2><p>For questions regarding these Terms of Service, please contact us at <strong>support@spc-academy.com</strong>.</p>'],
            ['key' => 'page_terms_updated', 'value' => 'October 2023'],
            ['key' => 'page_privacy', 'value' => '<p>At SPC Online Academy, we respect your privacy and are committed to protecting it through our compliance with this policy. This policy describes the types of information we may collect from you or that you may provide when you visit the website.</p><h2>1. Information We Collect</h2><p>We collect several types of information from and about users of our Platform, including:</p><ul><li><strong>Personal Information:</strong> Name, professional title, email address, phone number, and billing details required for course enrollment.</li><li><strong>Usage Data:</strong> Information about your internet connection, the equipment you use to access our Platform (Device IDs, IP addresses), course progress, quiz scores, and usage details.</li></ul><h2>2. How We Use Your Information</h2><p>We use information that we collect about you or that you provide to us, including any personal information:</p><ul><li>To present our Platform and its contents to you.</li><li>To process your enrollment and payments securely.</li><li>To track your academic progress and issue validated certificates.</li><li>To enforce our Terms of Service (e.g., detecting unauthorized account sharing).</li></ul><h2>3. Data Security and Third Parties</h2><p>We have implemented measures designed to secure your personal information from accidental loss and from unauthorized access. Payment transactions are encrypted using SSL technology and processed by certified third-party gateways (e.g., Paymob). We do not store your raw credit card information on our servers.</p><h2>4. Data Retention</h2><p>We retain your personal data for as long as your account is active or as needed to provide you services. You may request deletion of your account and associated data at any time by contacting support.</p><h2>5. Your Rights</h2><p>You have the right to:</p><ul><li>Access and receive a copy of your personal data.</li><li>Request correction of inaccurate data.</li><li>Request deletion of your account and personal data.</li><li>Opt out of marketing communications at any time.</li></ul><h2>6. Cookies</h2><p>We use cookies and similar tracking technologies to track activity on our Platform and hold certain information. You can instruct your browser to refuse all cookies or to indicate when a cookie is being sent.</p><h2>7. Changes to This Policy</h2><p>We may update our Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page and updating the "Effective Date" at the top.</p><h2>8. Contact Us</h2><p>If you have any questions about this Privacy Policy, please contact us at <strong>support@spc-academy.com</strong>.</p>'],
            ['key' => 'page_privacy_updated', 'value' => 'October 2023'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
