<?php

namespace App\Http\Controllers;

use App\Models\Official;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OfficialController extends Controller
{
    public function index(Request $request)
    {
        $editId = $request->input('edit');

        $officials = Official::whereNull('archived_at')->orderBy('sort_order')->orderBy('name')->get();

        $editOfficial = null;
        if ($editId) {
            $editOfficial = Official::whereNull('archived_at')->findOrFail($editId);
        }

        return view('admin.officials', compact('officials', 'editOfficial'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'official_id' => 'required|integer',
            'name' => 'required|string|max:200',
            'position' => 'required|string|max:150',
            'term_start' => 'nullable|date',
            'term_end' => 'nullable|date',
            'sort_order' => 'required|integer|min:0',
            'status' => 'required|in:active,inactive',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $id = $request->official_id;
        $data = $request->only(['name', 'position', 'term_start', 'term_end', 'sort_order', 'status']);

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('assets/uploads/officials'), $filename);

            // Delete old photo if it exists
            if ($id > 0) {
                $oldOfficial = Official::find($id);
                if ($oldOfficial && $oldOfficial->photo && file_exists(public_path('assets/uploads/' . $oldOfficial->photo))) {
                    @unlink(public_path('assets/uploads/' . $oldOfficial->photo));
                }
            }

            $data['photo'] = 'officials/' . $filename;
        }

        if ($id > 0) {
            $official = Official::findOrFail($id);
            $official->update($data);
            ActivityLog::log('UPDATE_OFFICIAL', 'Officials', "Updated official profile: {$request->name}");
            $msg = 'Official updated successfully.';
        } else {
            Official::create($data);
            ActivityLog::log('ADD_OFFICIAL', 'Officials', "Added new official: {$request->name}");
            $msg = 'Official added successfully.';
        }

        return redirect()->route('admin.officials')->with('success', $msg);
    }

    public function archive($id)
    {
        $official = Official::findOrFail($id);

        $official->update([
            'status' => 'inactive',
            'archived_at' => now(),
            'archived_by' => Auth::id()
        ]);

        ActivityLog::log('ARCHIVE_OFFICIAL', 'Officials', "Archived official: {$official->name}");

        return redirect()->route('admin.officials')->with('success', 'Official archived safely. You can restore it from Archive.');
    }

    public function delete($id)
    {
        $official = Official::findOrFail($id);
        
        // Delete photo from disk
        if ($official->photo && file_exists(public_path('assets/uploads/' . $official->photo))) {
            @unlink(public_path('assets/uploads/' . $official->photo));
        }

        ActivityLog::log('DELETE_OFFICIAL', 'Officials', "Deleted official: {$official->name}");
        $official->delete();

        return redirect()->route('admin.officials')->with('success', 'Official deleted successfully.');
    }
}
