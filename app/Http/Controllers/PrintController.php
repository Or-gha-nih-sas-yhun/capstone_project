<?php

namespace App\Http\Controllers;

use App\Models\Request as CertificateRequest;
use App\Models\Official;
use App\Models\Summon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrintController extends Controller
{
    public function print($id, Request $request)
    {
        $certReq = CertificateRequest::with(['resident', 'certificate', 'payment', 'approver'])
            ->findOrFail($id);

        // Security check: residents can only print their own approved/released requests
        if (Auth::user()->role === 'resident') {
            if (Auth::user()->resident_id !== $certReq->resident_id) {
                abort(403, 'Unauthorized.');
            }
            if (!in_array($certReq->status, ['approved', 'released'])) {
                abort(403, 'Document is not ready for printing.');
            }
        }

        // Fetch active officials for sidebar list
        $officials = Official::getActiveOfficials();

        // Resolve real officials from officers list
        $captain = Official::getCaptain();
        $captainName = $captain ? $captain->name : 'PUNONG BARANGAY';
        $secretary = Official::getSecretary();
        $secName = $secretary ? $secretary->name : 'BARANGAY SECRETARY';
        $treasurer = Official::getTreasurer();
        $treasName = $treasurer ? $treasurer->name : 'BARANGAY TREASURER';
        $skChairman = Official::getSkChairman();
        $skName = $skChairman ? $skChairman->name : 'SK CHAIRPERSON';
        $kagawads = Official::getKagawads();

        // Auto print check
        $autoPrint = $request->has('print');

        // Determine view from template_file column
        $tpl = $certReq->certificate->template_file ?? 'certificate.php';
        $viewName = 'print.general';

        if (str_contains($tpl, 'indigency')) {
            $viewName = 'print.indigency';
        } elseif (str_contains($tpl, 'residency')) {
            $viewName = 'print.residency';
        } elseif (str_contains($tpl, 'moral')) {
            $viewName = 'print.moral';
        } elseif (str_contains($tpl, 'clearance')) {
            $viewName = 'print.clearance';
        }

        return view($viewName, compact(
            'certReq',
            'officials',
            'captain',
            'captainName',
            'secretary',
            'secName',
            'treasurer',
            'treasName',
            'skChairman',
            'skName',
            'kagawads',
            'autoPrint'
        ));
    }

    public function printSummon($id, $formType, Request $request)
    {
        $summon = Summon::with(['complainantResident', 'respondentResident', 'hearings'])
            ->findOrFail($id);

        // Security check: residents can only print/view their own cases
        if (Auth::user()->role === 'resident') {
            $resident = Auth::user()->resident;
            if (!$resident || ($summon->complainant_resident_id !== $resident->id && $summon->respondent_resident_id !== $resident->id)) {
                abort(403, 'Unauthorized.');
            }
        }

        // Find Punong Barangay from real officers list
        $captain = Official::getCaptain();
        $captainName = $captain ? $captain->name : 'PUNONG BARANGAY';

        // Auto print check
        $autoPrint = $request->has('print');

        return view('print.kp_form', compact('summon', 'formType', 'captainName', 'autoPrint'));
    }
}
