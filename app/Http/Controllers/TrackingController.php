<?php

namespace App\Http\Controllers;

use App\Models\Request as CertificateRequest;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function track(Request $request)
    {
        $tracking = $request->input('tracking') ?? $request->query('tracking');
        $result = null;
        $error = '';
        $searched = false;

        if ($tracking) {
            $searched = true;
            $result = CertificateRequest::with(['resident', 'certificate', 'payment', 'processor'])
                ->where('tracking_number', trim($tracking))
                ->first();

            if (!$result) {
                $error = 'No request found with tracking number: ' . htmlspecialchars($tracking);
            }
        }

        return view('track', compact('result', 'error', 'searched', 'tracking'));
    }
}
