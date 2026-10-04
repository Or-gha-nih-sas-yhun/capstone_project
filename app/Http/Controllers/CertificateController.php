<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CertificateController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $editId = $request->input('edit');

        $query = Certificate::whereNull('archived_at');

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
        }

        $certificates = $query->orderBy('name')->paginate(8);

        $editCertificate = null;
        if ($editId) {
            $editCertificate = Certificate::findOrFail($editId);
        }

        return view('admin.certificates', compact('certificates', 'editCertificate', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'certificate_id' => 'required|integer',
            'name' => 'required|string|max:200',
            'category' => 'required|string|max:80',
            'fee' => 'required|numeric|min:0',
            'processing_days' => 'required|integer|min:1',
            'requirements' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $id = $request->certificate_id;

        // Auto-assign template file name based on certificate name
        $nameLower = strtolower($request->name);
        $templateFile = 'certificate.php';
        if (str_contains($nameLower, 'indigency')) {
            $templateFile = 'certificate_indigency.php';
        } elseif (str_contains($nameLower, 'residency') || str_contains($nameLower, 'resident')) {
            $templateFile = 'certificate_residency.php';
        } elseif (str_contains($nameLower, 'moral')) {
            $templateFile = 'certificate_moral.php';
        } elseif (str_contains($nameLower, 'clearance')) {
            $templateFile = 'certificate_clearance.php';
        }

        $data = $request->only(['name', 'category', 'fee', 'processing_days', 'requirements', 'description']);
        $data['template_file'] = $templateFile;

        if ($id > 0) {
            $cert = Certificate::findOrFail($id);
            $cert->update($data);
            ActivityLog::log('UPDATE_CERTIFICATE', 'Certificates', "Updated certificate ID $id");
            $msg = 'Certificate type updated successfully.';
        } else {
            $data['status'] = 'active';
            Certificate::create($data);
            ActivityLog::log('ADD_CERTIFICATE', 'Certificates', "Added new certificate: {$request->name}");
            $msg = 'Certificate type created successfully.';
        }

        return redirect()->route('admin.certificates')->with('success', $msg);
    }

    public function delete($id)
    {
        $cert = Certificate::findOrFail($id);
        $userId = Auth::id();

        $cert->update([
            'status' => 'inactive',
            'archived_at' => now(),
            'archived_by' => $userId
        ]);

        ActivityLog::log('ARCHIVE_CERTIFICATE', 'Certificates', "Archived certificate type ID $id");

        return redirect()->route('admin.certificates')->with('success', 'Certificate type archived successfully. It can be restored from Archive.');
    }
}
