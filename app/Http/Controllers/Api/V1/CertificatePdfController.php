<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class CertificatePdfController extends Controller
{
    public function download(Request $request, Certificate $certificate)
    {
        abort_unless($certificate->user_id === $request->user()->id, 403);
        $certificate->load('user', 'course.instructor', 'course.category');

        $pdf = Pdf::loadView('certificates.template', ['certificate' => $certificate, 'settings' => $this->getSettings()]);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download("SPC-Certificate-{$certificate->certificate_number}.pdf");
    }

    public function preview(Request $request, Certificate $certificate)
    {
        abort_unless($certificate->user_id === $request->user()->id, 403);
        $certificate->load('user', 'course.instructor', 'course.category');

        $pdf = Pdf::loadView('certificates.template', ['certificate' => $certificate, 'settings' => $this->getSettings()]);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->stream("certificate-{$certificate->certificate_number}.pdf");
    }

    private function getSettings(): array
    {
        return Setting::whereIn('key', ['site_name', 'logo', 'primary_color', 'contact_email'])->pluck('value', 'key')->toArray();
    }
}
