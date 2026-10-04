<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $editId = $request->input('edit');
        $search = $request->input('search');

        $query = User::whereIn('role', ['admin', 'staff'])->whereNull('archived_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('username')->paginate(8);

        $editUser = null;
        if ($editId) {
            $editUser = User::findOrFail($editId);
        }

        return view('admin.users', compact('users', 'editUser', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'username' => 'required|string|max:100|unique:users,username,' . $request->user_id,
            'email' => 'required|email|max:150|unique:users,email,' . $request->user_id,
            'role' => 'required|in:admin,staff',
            'status' => 'required|in:active,inactive,suspended',
            'password' => $request->user_id > 0 ? 'nullable|string|min:6' : 'required|string|min:6',
        ]);

        $id = $request->user_id;

        $data = $request->only(['username', 'email', 'role', 'status']);
        if ($request->password) {
            $data['password'] = Hash::make($request->password);
        }

        if ($id > 0) {
            $user = User::findOrFail($id);
            $user->update($data);
            ActivityLog::log('UPDATE_USER', 'Users', "Updated admin/staff user: {$request->username}");
            $msg = 'User updated successfully.';
        } else {
            User::create($data);
            ActivityLog::log('ADD_USER', 'Users', "Created admin/staff user: {$request->username}");
            $msg = 'User added successfully.';
        }

        return redirect()->route('admin.users')->with('success', $msg);
    }

    public function delete($id)
    {
        $user = User::findOrFail($id);
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users')->with('error', 'You cannot delete your own account.');
        }

        $user->update([
            'status' => 'inactive',
            'archived_at' => now(),
            'archived_by' => auth()->id()
        ]);

        ActivityLog::log('ARCHIVE_USER', 'Users', "Archived admin/staff user: {$user->username}");

        return redirect()->route('admin.users')->with('success', 'User archived successfully.');
    }
}
