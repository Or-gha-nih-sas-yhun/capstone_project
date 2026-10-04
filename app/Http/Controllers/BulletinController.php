<?php

namespace App\Http\Controllers;

use App\Models\BulletinAnnouncement;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class BulletinController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $category = $request->input('category', 'all');

        $query = BulletinAnnouncement::with('creator');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($category !== 'all') {
            $query->where('category', $category);
        }

        $bulletins = $query->orderBy('is_pinned', 'desc')
            ->orderBy('published_at', 'desc')
            ->paginate(8);

        return view('admin.bulletins', compact('bulletins', 'search', 'category'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|string|max:100',
            'is_pinned' => 'nullable|boolean',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $imagePath = $this->storeImage($request);

        $bulletin = BulletinAnnouncement::create([
            'title' => $request->title,
            'content' => $request->content,
            'image_path' => $imagePath,
            'category' => $request->category,
            'is_pinned' => $request->has('is_pinned'),
            'published_at' => now(),
            'created_by' => Auth::id(),
        ]);

        ActivityLog::log('CREATE_BULLETIN', 'Bulletins', "Created announcement: {$request->title}");

        return back()->with('success', 'Announcement published successfully.');
    }

    public function update(Request $request)
    {
        $request->validate([
            'bulletin_id' => 'required|exists:bulletin_announcements,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|string|max:100',
            'is_pinned' => 'nullable|boolean',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $bulletin = BulletinAnnouncement::findOrFail($request->bulletin_id);
        $imagePath = $bulletin->image_path;
        if ($request->hasFile('image')) {
            $this->deleteImage($imagePath);
            $imagePath = $this->storeImage($request);
        }
        $bulletin->update([
            'title' => $request->title,
            'content' => $request->content,
            'image_path' => $imagePath,
            'category' => $request->category,
            'is_pinned' => $request->has('is_pinned'),
        ]);

        ActivityLog::log('UPDATE_BULLETIN', 'Bulletins', "Updated announcement: {$request->title}");

        return back()->with('success', 'Announcement updated successfully.');
    }

    public function delete($id)
    {
        $bulletin = BulletinAnnouncement::findOrFail($id);
        $title = $bulletin->title;
        $this->deleteImage($bulletin->image_path);
        $bulletin->delete();

        ActivityLog::log('DELETE_BULLETIN', 'Bulletins', "Deleted announcement: {$title}");

        return back()->with('success', 'Announcement deleted successfully.');
    }

    public function residentIndex()
    {
        $bulletins = BulletinAnnouncement::with('creator')
            ->orderBy('is_pinned', 'desc')
            ->orderBy('published_at', 'desc')
            ->paginate(8);

        return view('resident.bulletins', compact('bulletins'));
    }

    private function storeImage(Request $request): ?string
    {
        if (!$request->hasFile('image')) {
            return null;
        }

        $directory = public_path('assets/uploads/bulletins');
        File::ensureDirectoryExists($directory);
        $file = $request->file('image');
        $filename = now()->format('YmdHis') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move($directory, $filename);

        return 'bulletins/' . $filename;
    }

    private function deleteImage(?string $imagePath): void
    {
        if ($imagePath) {
            File::delete(public_path('assets/uploads/' . $imagePath));
        }
    }
}
