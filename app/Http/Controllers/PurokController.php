<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Purok;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PurokController extends Controller
{
    public function index(Request $request)
    {
        $editId = $request->input('edit');

        $puroks = Purok::orderBy('sort_order')->orderBy('name')->get();

        $editPurok = $editId ? Purok::findOrFail($editId) : null;

        return view('admin.puroks', compact('puroks', 'editPurok'));
    }

    public function store(Request $request)
    {
        $id = (int) $request->input('purok_id', 0);

        $request->validate([
            'purok_id'   => 'required|integer',
            'name'       => ['required', 'string', 'max:100', Rule::unique('puroks', 'name')->ignore($id ?: null)],
            'sort_order' => 'required|integer|min:0',
            'status'     => 'required|in:active,inactive',
        ]);

        $data = [
            'name'       => trim($request->name),
            'sort_order' => $request->sort_order,
            'status'     => $request->status,
        ];

        if ($id > 0) {
            $purok = Purok::findOrFail($id);
            $oldName = $purok->name;
            $purok->update($data);

            // Residents store the purok by name, so a rename has to carry
            // across or their records would point at a name that no longer
            // appears in the list.
            if ($oldName !== $data['name']) {
                \App\Models\Resident::where('purok', $oldName)->update(['purok' => $data['name']]);
            }

            ActivityLog::log('UPDATE_PUROK', 'Puroks', "Renamed purok {$oldName} to {$data['name']}");
            $msg = 'Purok updated successfully.';
        } else {
            Purok::create($data);
            ActivityLog::log('ADD_PUROK', 'Puroks', "Added purok: {$data['name']}");
            $msg = 'Purok added successfully.';
        }

        return redirect()->route('admin.puroks')->with('success', $msg);
    }

    public function delete($id)
    {
        $purok = Purok::findOrFail($id);
        $count = $purok->residentCount();

        // Deleting a purok that residents still reference would leave their
        // records pointing at a name no longer offered, so deactivate instead.
        if ($count > 0) {
            $purok->update(['status' => 'inactive']);
            ActivityLog::log('DEACTIVATE_PUROK', 'Puroks', "Deactivated purok {$purok->name} ({$count} residents)");

            return redirect()->route('admin.puroks')->with(
                'success',
                "\"{$purok->name}\" has {$count} resident record(s), so it was set to inactive instead of deleted. "
                . 'It no longer appears in dropdowns but existing records keep their value.'
            );
        }

        $name = $purok->name;
        $purok->delete();
        ActivityLog::log('DELETE_PUROK', 'Puroks', "Deleted purok: {$name}");

        return redirect()->route('admin.puroks')->with('success', "Purok \"{$name}\" deleted.");
    }
}
