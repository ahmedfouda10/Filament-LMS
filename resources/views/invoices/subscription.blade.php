<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12pt; color: #333; margin: 30px; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #236bba; padding-bottom: 15px; margin-bottom: 20px; }
        .title { font-size: 24pt; color: #236bba; font-weight: bold; }
        .invoice-id { font-size: 10pt; color: #888; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th { background: #f5f5f5; text-align: left; padding: 10px; border-bottom: 1px solid #ddd; }
        td { padding: 10px; border-bottom: 1px solid #eee; }
        .total { font-size: 14pt; font-weight: bold; text-align: right; margin-top: 20px; }
        .footer { margin-top: 40px; font-size: 9pt; color: #999; text-align: center; }
    </style>
</head>
<body>
    <div class="title">{{ $settings['site_name'] ?? 'SPC Online Academy' }}</div>
    <div class="invoice-id">Invoice #INV-SUB-{{ str_pad($subscription->id, 4, '0', STR_PAD_LEFT) }}</div>
    <p><strong>Date:</strong> {{ $subscription->start_date->format('F d, Y') }}</p>
    <p><strong>Student:</strong> {{ $subscription->user->name }} ({{ $subscription->user->email }})</p>

    <table>
        <thead><tr><th>Description</th><th>Duration</th><th>Amount</th></tr></thead>
        <tbody>
            <tr>
                <td>{{ $subscription->plan->name }} Subscription</td>
                <td>{{ $subscription->plan->duration_months }} month(s)</td>
                <td>EGP {{ number_format($subscription->plan->total_price, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="total">Total: EGP {{ number_format($subscription->plan->total_price, 2) }}</div>
    <p style="text-align:right; color:#4CAF50; font-weight:bold;">PAID</p>

    <div class="footer">
        {{ $settings['site_name'] ?? 'SPC Online Academy' }} | {{ $settings['address'] ?? '' }} | {{ $settings['contact_email'] ?? '' }}
    </div>
</body>
</html>
