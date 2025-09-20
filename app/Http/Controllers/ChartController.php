<?php

namespace App\Http\Controllers;

use App\Models\Chart;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ChartController extends Controller
{
    public function show($id)
    {
    $chart = Chart::with(['chartEntries.album.artist', 'chartEntries.album.trackListens', 'user'])->findOrFail($id);
        return Inertia::render('Chart/Show', [
            'chart' => $chart,
        ]);
    }

    public function destroy($id)
    {
        $chart = Chart::findOrFail($id);
        $chart->delete();
        return redirect()->back()->with('success', 'Chart deleted successfully.');
    }
}
