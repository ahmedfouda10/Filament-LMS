<!DOCTYPE html>
<html dir="ltr">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 0; size: A4 landscape; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; background: #fff; }
        .certificate { width: 297mm; height: 210mm; position: relative; padding: 20mm; }
        .border-outer { position: absolute; top: 8mm; left: 8mm; right: 8mm; bottom: 8mm; border: 3px solid {{ $settings['primary_color'] ?? '#236bba' }}; }
        .border-inner { position: absolute; top: 12mm; left: 12mm; right: 12mm; bottom: 12mm; border: 1px solid {{ $settings['primary_color'] ?? '#236bba' }}; }
        .content { position: relative; text-align: center; padding: 15mm 20mm; z-index: 10; }
        .logo { max-height: 15mm; margin-bottom: 5mm; }
        .header-text { font-size: 12pt; color: #666; text-transform: uppercase; letter-spacing: 4px; margin-bottom: 3mm; }
        .title { font-size: 36pt; color: {{ $settings['primary_color'] ?? '#236bba' }}; font-weight: bold; margin-bottom: 5mm; letter-spacing: 2px; }
        .subtitle { font-size: 11pt; color: #888; margin-bottom: 8mm; }
        .student-name { font-size: 28pt; color: #1a1a1a; font-weight: bold; margin-bottom: 5mm; border-bottom: 2px solid {{ $settings['primary_color'] ?? '#236bba' }}; display: inline-block; padding-bottom: 3mm; }
        .course-text { font-size: 11pt; color: #666; margin-bottom: 3mm; }
        .course-name { font-size: 16pt; color: #1a1a1a; font-weight: bold; margin-bottom: 8mm; }
        .details { margin-top: 5mm; display: table; width: 100%; }
        .detail-left, .detail-center, .detail-right { display: table-cell; vertical-align: top; width: 33.33%; text-align: center; }
        .detail-label { font-size: 8pt; color: #999; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 2mm; }
        .detail-value { font-size: 10pt; color: #333; font-weight: bold; }
        .cert-number { position: absolute; bottom: 15mm; right: 20mm; font-size: 7pt; color: #ccc; }
        .valid-until { position: absolute; bottom: 15mm; left: 20mm; font-size: 7pt; color: #ccc; }
        .watermark { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); font-size: 72pt; color: rgba(35, 107, 186, 0.03); font-weight: bold; white-space: nowrap; z-index: 1; }
    </style>
</head>
<body>
    <div class="certificate">
        <div class="border-outer"></div>
        <div class="border-inner"></div>
        <div class="watermark">{{ $settings['site_name'] ?? 'SPC Online Academy' }}</div>
        <div class="content">
            @if(!empty($settings['logo']))
                <img src="{{ public_path($settings['logo']) }}" class="logo" alt="Logo">
            @endif
            <div class="header-text">{{ $settings['site_name'] ?? 'SPC Online Academy' }}</div>
            <div class="title">Certificate of Completion</div>
            <div class="subtitle">This certificate is proudly presented to</div>
            <div class="student-name">{{ $certificate->student_name }}</div>
            <div class="course-text">For successfully completing the course</div>
            <div class="course-name">{{ $certificate->course->title }}</div>
            <div class="details">
                <div class="detail-left">
                    <div class="detail-label">Instructor</div>
                    <div class="detail-value">{{ $certificate->course->instructor->name ?? 'N/A' }}</div>
                </div>
                <div class="detail-center">
                    <div class="detail-label">Date Issued</div>
                    <div class="detail-value">{{ $certificate->issued_at->format('F d, Y') }}</div>
                </div>
                <div class="detail-right">
                    <div class="detail-label">Category</div>
                    <div class="detail-value">{{ $certificate->course->category->name ?? 'N/A' }}</div>
                </div>
            </div>
        </div>
        <div class="valid-until">Valid Until: {{ $certificate->valid_until?->format('F d, Y') ?? 'Lifetime' }}</div>
        <div class="cert-number">Certificate ID: {{ $certificate->certificate_number }}</div>
    </div>
</body>
</html>
