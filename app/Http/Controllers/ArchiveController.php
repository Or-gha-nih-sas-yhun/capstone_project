<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Resident;
use App\Models\Certificate;
use App\Models\Request as CertificateRequest;
use App\Models\Summon;
use App\Models\Official;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ArchiveController extends Controller
{
    public function index()
    {
        $residents = Resident::whereNotNull('archived_at')->orderBy('archived_at', 'desc')->get();
        $certificates = Certificate::whereNotNull('archived_at')->orderBy('archived_at', 'desc')->get();
        $requests = CertificateRequest::with(['resident', 'certificate'])->whereNotNull('archived_at')->orderBy('archived_at', 'desc')->get();
        $users = User::whereIn('role', ['admin', 'staff'])->whereNotNull('archived_at')->orderBy('archived_at', 'desc')->get();
        $summons = Summon::whereNotNull('archived_at')->orderBy('archived_at', 'desc')->get();
        $officials = Official::whereNotNull('archived_at')->orderBy('archived_at', 'desc')->get();

        return view('admin.archive', compact('residents', 'certificates', 'requests', 'users', 'summons', 'officials'));
    }

    public function restore($type, $id)
    {
        $restored = false;
        $name = '';

        if ($type === 'resident') {
            $resident = Resident::findOrFail($id);
            $resident->update([
                'status' => 'active',
                'archived_at' => null,
                'archived_by' => null
            ]);
            // Also restore user if linked
            if ($resident->user) {
                $resident->user->update([
                    'status' => 'active',
                    'archived_at' => null,
                    'archived_by' => null
                ]);
            }
            $name = $resident->full_name;
            $restored = true;
        } elseif ($type === 'certificate') {
            $cert = Certificate::findOrFail($id);
            $cert->update([
                'status' => 'active',
                'archived_at' => null,
                'archived_by' => null
            ]);
            $name = $cert->name;
            $restored = true;
        } elseif ($type === 'request') {
            $req = CertificateRequest::findOrFail($id);
            $req->update([
                'archived_at' => null,
                'archived_by' => null
            ]);
            $name = $req->tracking_number;
            $restored = true;
        } elseif ($type === 'user') {
            $user = User::findOrFail($id);
            $user->update([
                'status' => 'active',
                'archived_at' => null,
                'archived_by' => null
            ]);
            $name = $user->username;
            $restored = true;
        } elseif ($type === 'summon') {
            $summon = Summon::findOrFail($id);
            $summon->update([
                'archived_at' => null,
                'archived_by' => null
            ]);
            $name = $summon->case_number;
            $restored = true;
        } elseif ($type === 'official') {
            $official = Official::findOrFail($id);
            $official->update([
                'status' => 'active',
                'archived_at' => null,
                'archived_by' => null
            ]);
            $name = $official->name;
            $restored = true;
        }

        if ($restored) {
            ActivityLog::log('RESTORE', 'Archive', "Restored archived {$type}: {$name}");
            return redirect()->route('admin.archive')->with('success', ucfirst($type) . ' restored successfully.');
        }

        return redirect()->route('admin.archive')->with('error', 'Invalid resource restoration type.');
    }
}
