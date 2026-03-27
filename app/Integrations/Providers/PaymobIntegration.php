<?php

namespace App\Integrations\Providers;

use App\Integrations\Contracts\Integration;
use App\Models\Setting;
use Filament\Forms;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;

class PaymobIntegration implements Integration
{
    public function slug(): string
    {
        return 'paymob';
    }

    public function name(): string
    {
        return 'Paymob';
    }

    public function description(): string
    {
        return 'Accept online payments via credit cards, mobile wallets, and installments through Paymob payment gateway.';
    }

    public function icon(): string
    {
        return 'heroicon-o-credit-card';
    }

    public function category(): string
    {
        return 'Payment Gateway';
    }

    public function settingsKeys(): array
    {
        return [
            'paymob_api_key',
            'paymob_secret_key',
            'paymob_public_key',
            'paymob_hmac_secret',
            'paymob_integration_id',
            'paymob_wallet_integration_id',
            'paymob_iframe_id',
        ];
    }

    public function requiredKeys(): array
    {
        return [
            'paymob_api_key',
            'paymob_hmac_secret',
            'paymob_integration_id',
        ];
    }

    public function formSchema(): array
    {
        $appUrl = config('app.url');
        $webhookUrl = $appUrl . '/api/v1/payments/webhook';
        $callbackUrl = $appUrl . '/api/v1/payments/callback';

        return [
            Section::make('How to get your credentials')
                ->icon('heroicon-o-information-circle')
                ->iconColor('info')
                ->schema([
                    TextEntry::make('instructions')
                        ->label('')
                        ->state(new \Illuminate\Support\HtmlString(
                            '<ol style="list-style:decimal; padding-left:20px; line-height:2;">' .
                            '<li>Go to <strong><a href="https://accept.paymob.com" target="_blank" style="color:#2563eb; text-decoration:underline;">accept.paymob.com</a></strong> and sign in</li>' .
                            '<li><strong>API Key, Secret Key, Public Key</strong> → Settings → Account Info → click "View"</li>' .
                            '<li><strong>HMAC Secret</strong> → Settings → Account Info → HMAC field</li>' .
                            '<li><strong>Integration ID</strong> → Developers → Payment Integrations → click on the integration to see its ID</li>' .
                            '<li><strong>iFrame ID</strong> → Developers → iFrames → use the ID number shown</li>' .
                            '</ol>'
                        ))
                        ->html(),
                ])
                ->collapsed()
                ->collapsible(),

            Section::make('API Credentials')
                ->schema([
                    Forms\Components\TextInput::make('paymob_api_key')
                        ->label('API Key')
                        ->password()
                        ->revealable()
                        ->helperText('Settings → Account Info → API Key → View'),
                    Forms\Components\TextInput::make('paymob_secret_key')
                        ->label('Secret Key')
                        ->password()
                        ->revealable()
                        ->helperText('Settings → Account Info → Secret Key → View'),
                    Forms\Components\TextInput::make('paymob_public_key')
                        ->label('Public Key')
                        ->helperText('Settings → Account Info → Public Key → View'),
                    Forms\Components\TextInput::make('paymob_hmac_secret')
                        ->label('HMAC Secret')
                        ->password()
                        ->revealable()
                        ->helperText('Settings → Account Info → HMAC'),
                ])
                ->columns(2),

            Section::make('Payment Integration')
                ->schema([
                    Forms\Components\TextInput::make('paymob_integration_id')
                        ->label('Card Integration ID')
                        ->helperText('Developers → Payment Integrations → your card integration ID'),
                    Forms\Components\TextInput::make('paymob_wallet_integration_id')
                        ->label('Wallet Integration ID')
                        ->helperText('Developers → Payment Integrations → your wallet integration ID (optional)'),
                    Forms\Components\TextInput::make('paymob_iframe_id')
                        ->label('iFrame ID')
                        ->helperText('Developers → iFrames → your iframe ID number'),
                ])
                ->columns(3),

            Section::make('Callback URLs')
                ->icon('heroicon-o-link')
                ->iconColor('warning')
                ->description('Copy these URLs and paste them in your Paymob Dashboard → Developers → Payment Integrations → Edit')
                ->schema([
                    TextEntry::make('webhook_url')
                        ->label('Transaction processed callback')
                        ->state(new \Illuminate\Support\HtmlString(
                            '<code style="background-color:#f5f5f5; padding:2px 4px; border-radius:4px; font-size:12px user-select:all display:block">' . $webhookUrl . '</code>'
                        ))
                        ->copyable()
                        ->helperText('Server-to-server notification when payment is processed.'),
                    TextEntry::make('callback_url')
                        ->label('Transaction response callback')
                        ->state(new \Illuminate\Support\HtmlString(
                            '<code style="background-color:#f5f5f5; padding:2px 4px; border-radius:4px; font-size:12px user-select:all display:block">' . $callbackUrl . '</code>'
                        ))
                        ->copyable()
                        ->helperText('Browser redirect URL after payment (redirects student to success/failure page).'),
                ]),
        ];
    }

    public function isConnected(): bool
    {
        foreach ($this->requiredKeys() as $key) {
            $value = Setting::get($key);
            if (empty($value)) {
                return false;
            }
        }

        return true;
    }
}
