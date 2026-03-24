<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class SubscriptionInvoiceController extends Controller
{
    public function download(Request $request, Subscription $subscription)
    {
        abort_unless($subscription->user_id === $request->user()->id, 403);

        $subscription->load('plan', 'user');
        $settings = \App\Models\Setting::whereIn('key', ['site_name', 'logo', 'contact_email', 'address'])->pluck('value', 'key')->toArray();

        $pdf = Pdf::loadView('invoices.subscription', [
            'subscription' => $subscription,
            'settings' => $settings,
        ])->setPaper('A4', 'portrait');

        return $pdf->download("Invoice-SUB-{$subscription->id}.pdf");
    }
}
