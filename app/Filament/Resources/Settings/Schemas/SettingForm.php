<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // General Settings
                Section::make('General Settings')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->schema([
                        Forms\Components\TextInput::make('site_name')
                            ->label('Site Name')
                            ->required(),
                        Forms\Components\FileUpload::make('logo')
                            ->label('Logo')
                            ->image()
                            ->directory('settings'),
                        Forms\Components\Textarea::make('site_description')
                            ->label('Site Description')
                            ->rows(2)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('contact_phone')
                            ->label('Contact Phone')
                            ->tel(),
                        Forms\Components\TextInput::make('contact_email')
                            ->label('Contact Email')
                            ->email(),
                        Forms\Components\TextInput::make('address')
                            ->label('Address'),
                        Forms\Components\TextInput::make('working_hours')
                            ->label('Working Hours'),
                        Forms\Components\TextInput::make('frontend_url')
                            ->label('Frontend URL')
                            ->url()
                            ->placeholder('https://spc.a2za1.com')
                            ->helperText('Used for payment callback redirects.'),
                    ])->columns(2),

                // Announcements
                Section::make('Announcements')
                    ->icon('heroicon-o-megaphone')
                    ->schema([
                        Forms\Components\Toggle::make('announcement_enabled')
                            ->label('Show Announcement'),
                        Forms\Components\TextInput::make('announcement_text')
                            ->label('Announcement Text'),
                        Forms\Components\Select::make('announcement_color')
                            ->label('Color')
                            ->options(['green' => 'Green', 'blue' => 'Blue', 'yellow' => 'Yellow', 'red' => 'Red']),
                        Forms\Components\TextInput::make('hero_video_url')
                            ->label('Hero Video URL')
                            ->url()
                            ->placeholder('https://youtube.com/watch?v=...'),
                    ])->columns(2),

                // Appearance
                Section::make('Appearance')
                    ->icon('heroicon-o-paint-brush')
                    ->schema([
                        Forms\Components\ColorPicker::make('primary_color')
                            ->label('Primary Color'),
                        Forms\Components\ColorPicker::make('secondary_color')
                            ->label('Secondary Color'),
                    ])->columns(2),

                // Social Media
                Section::make('Social Media')
                    ->icon('heroicon-o-globe-alt')
                    ->schema([
                        Forms\Components\TextInput::make('facebook_url')
                            ->label('Facebook URL')
                            ->url()
                            ->prefix('https://'),
                        Forms\Components\TextInput::make('twitter_url')
                            ->label('Twitter URL')
                            ->url()
                            ->prefix('https://'),
                        Forms\Components\TextInput::make('instagram_url')
                            ->label('Instagram URL')
                            ->url()
                            ->prefix('https://'),
                        Forms\Components\TextInput::make('linkedin_url')
                            ->label('LinkedIn URL')
                            ->url()
                            ->prefix('https://'),
                        Forms\Components\TextInput::make('youtube_url')
                            ->label('YouTube URL')
                            ->url()
                            ->prefix('https://'),
                    ])->columns(2),

                // SEO
                Section::make('SEO')
                    ->icon('heroicon-o-magnifying-glass')
                    ->schema([
                        Forms\Components\TextInput::make('meta_title')
                            ->label('Meta Title')
                            ->maxLength(70),
                        Forms\Components\Textarea::make('meta_description')
                            ->label('Meta Description')
                            ->rows(2)
                            ->maxLength(160),
                        Forms\Components\TextInput::make('meta_keywords')
                            ->label('Meta Keywords')
                            ->helperText('Comma-separated keywords'),
                    ])->columns(1),

                // Business Settings
                Section::make('Business Settings')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        Forms\Components\TextInput::make('currency')
                            ->label('Currency')
                            ->default('EGP')
                            ->disabled(),
                        Forms\Components\TextInput::make('platform_fee_percentage')
                            ->label('Platform Fee (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%'),
                        Forms\Components\TextInput::make('certificate_validity_years')
                            ->label('Certificate Validity (Years)')
                            ->numeric()
                            ->minValue(1)
                            ->suffix('years'),
                    ])->columns(3),

                // Social Login
                Section::make('Social Login')
                    ->icon('heroicon-o-user-group')
                    ->schema([
                        Forms\Components\TextInput::make('google_client_id')->label('Google Client ID')->password()->revealable(),
                        Forms\Components\TextInput::make('google_client_secret')->label('Google Client Secret')->password()->revealable(),
                        Forms\Components\TextInput::make('facebook_client_id')->label('Facebook Client ID')->password()->revealable(),
                        Forms\Components\TextInput::make('facebook_client_secret')->label('Facebook Client Secret')->password()->revealable(),
                    ])->columns(2),

                // Feature Flags
                Section::make('Feature Flags')
                    ->icon('heroicon-o-flag')
                    ->schema([
                        Forms\Components\Toggle::make('feature_social_login')->label('Social Login'),
                        Forms\Components\Toggle::make('feature_live_chat')->label('Live Chat'),
                        Forms\Components\Toggle::make('feature_push_notifications')->label('Push Notifications'),
                        Forms\Components\Toggle::make('feature_offline_downloads')->label('Offline Downloads'),
                        Forms\Components\Toggle::make('feature_messaging')->label('Messaging System'),
                    ])->columns(3),

                // System Limits
                Section::make('System Limits')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->schema([
                        Forms\Components\TextInput::make('max_offline_downloads')->label('Max Offline Downloads')->numeric()->minValue(1),
                        Forms\Components\TextInput::make('download_token_hours')->label('Download Token Validity (hours)')->numeric()->minValue(1),
                        Forms\Components\TextInput::make('max_devices_per_user')->label('Max Devices Per User')->numeric()->minValue(1),
                    ])->columns(3),

                // Installments
                Section::make('Installments')
                    ->icon('heroicon-o-calculator')
                    ->schema([
                        Forms\Components\Toggle::make('installment_enabled')->label('Installments Enabled'),
                        Forms\Components\TextInput::make('installment_providers')->label('Providers')->helperText('Comma-separated: valu,sympl'),
                    ])->columns(2),

                // Messaging
                Section::make('Messaging')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->schema([
                        Forms\Components\Toggle::make('messaging_enabled')->label('Messaging Enabled'),
                    ])->columns(1),

                // Maintenance
                Section::make('Maintenance')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->schema([
                        Forms\Components\Toggle::make('maintenance_mode')
                            ->label('Maintenance Mode')
                            ->helperText('When enabled, the site will show a maintenance page to visitors.')
                            ->onColor('danger')
                            ->offColor('success'),
                        Forms\Components\Textarea::make('maintenance_message')
                            ->label('Maintenance Message')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(1),
            ]);
    }
}
