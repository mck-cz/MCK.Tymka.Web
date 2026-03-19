<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Photo;
use App\Models\Team;
use App\Models\TeamMembership;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AlbumController extends Controller
{
    public function index()
    {
        $clubId = session('current_club_id');

        $albums = Album::where('club_id', $clubId)
            ->with(['team', 'createdBy'])
            ->withCount('photos')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('albums.index', compact('albums'));
    }

    public function create()
    {
        $clubId = session('current_club_id');
        $teams = Team::where('club_id', $clubId)->orderBy('name')->get();

        return view('albums.create', compact('teams'));
    }

    public function store(Request $request)
    {
        $clubId = session('current_club_id');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'team_id' => ['nullable', Rule::exists('teams', 'id')->where('club_id', $clubId)],
        ]);

        Album::create([
            ...$validated,
            'club_id' => $clubId,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('albums.index')
            ->with('success', __('messages.albums.created'));
    }

    public function show(Album $album)
    {
        $clubId = session('current_club_id');
        abort_unless($album->club_id === $clubId, 404);

        $album->load(['photos.uploadedBy', 'team', 'createdBy']);

        return view('albums.show', compact('album'));
    }

    public function uploadPhoto(Request $request, Album $album)
    {
        $clubId = session('current_club_id');
        abort_unless($album->club_id === $clubId, 404);

        $request->validate([
            'photo' => 'required|image|max:5120',
            'caption' => 'nullable|string|max:255',
        ]);

        $path = ImageService::store($request->file('photo'), 'albums/' . $album->id, 1920, 1920, 80);

        Photo::create([
            'album_id' => $album->id,
            'uploaded_by' => Auth::id(),
            'file_path' => $path,
            'caption' => $request->input('caption'),
        ]);

        return back()->with('success', __('messages.albums.photo_uploaded'));
    }

    public function destroyPhoto(Photo $photo)
    {
        $clubId = session('current_club_id');
        abort_unless($photo->album->club_id === $clubId, 404);
        abort_unless($photo->uploaded_by === Auth::id(), 403);

        Storage::disk('public')->delete($photo->file_path);
        if ($photo->thumbnail_path) {
            Storage::disk('public')->delete($photo->thumbnail_path);
        }
        $photo->delete();

        return back()->with('success', __('messages.albums.photo_deleted'));
    }

    public function destroy(Album $album)
    {
        $clubId = session('current_club_id');
        abort_unless($album->club_id === $clubId, 404);
        abort_unless($album->created_by === Auth::id(), 403);

        // Delete photo files
        foreach ($album->photos as $photo) {
            Storage::disk('public')->delete($photo->file_path);
            if ($photo->thumbnail_path) {
                Storage::disk('public')->delete($photo->thumbnail_path);
            }
        }
        $album->photos()->delete();
        $album->delete();

        return redirect()->route('albums.index')
            ->with('success', __('messages.albums.deleted'));
    }
}
