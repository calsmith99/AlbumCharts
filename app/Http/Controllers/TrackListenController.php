<?php
namespace App\Http\Controllers;

use App\Models\TrackListen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrackListenController extends Controller
{
    public function recentForAlbum($albumId, Request $request)
    {
        $userId = Auth::id();
        $trackListens = TrackListen::where('user_id', $userId)
            ->where('album_id', $albumId)
            ->orderBy('listened_at', 'desc')
            ->get();
        return response()->json($trackListens);
    }
}
