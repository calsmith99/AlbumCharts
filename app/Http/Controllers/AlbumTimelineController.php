<?php
namespace App\Http\Controllers;

use App\Models\ChartEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlbumTimelineController extends Controller
{
    public function paginatedFullListens(Request $request)
    {
        $userId = Auth::id();
        $perPage = 20;
        $page = (int) $request->input('page', 1);
        $query = ChartEntry::where('completed_album', true)
            ->whereHas('chart', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->with(['album.artist'])
            ->orderBy('created_at', 'desc');
        $paginator = $query->paginate($perPage, ['*'], 'page', $page);
        $albums = $paginator->getCollection()->map(function ($entry) {
            return [
                'id' => $entry->id, // ChartEntry id, not album id
                'album_id' => $entry->album->id,
                'name' => $entry->album->name,
                'artist_name' => $entry->album->artist->name,
                'image_url' => $entry->album->image_url,
                'listened_at' => $entry->created_at, // ChartEntry created_at
            ];
        })->values();
        return response()->json([
            'albums' => $albums,
            'hasMore' => $paginator->hasMorePages(),
        ]);
    }
}
