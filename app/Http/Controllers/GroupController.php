<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GroupController extends Controller
{
    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);

        $group = Group::create([
            'name' => $request->name,
            'owner_id' => $request->user()->id,
        ]);

        // Add owner as member
        $group->users()->attach($request->user()->id);

        return redirect()->route('groups.show', $group->id);
    }

    public function show(Group $group)
    {
        // If an authenticated user visits the group, auto-join them as a member
        $user = auth()->user();
        if ($user) {
            // attach without duplicating
            if (!$group->users()->where('user_id', $user->id)->exists()) {
                $group->users()->attach($user->id);
            }
        }

        $group->load('users');

        // Precompute grid for server-side rendering (includes newly attached user)
        $gridResponse = $this->grid($group);
        $gridPayload = $gridResponse->getData(true);
        $gridData = $gridPayload['grid'] ?? null;
        $aggregated = $gridPayload['aggregated_chart'] ?? null;

        return Inertia::render('Groups/Show', [
            'group' => $group,
            'grid' => $gridData,
            'aggregated_chart' => $aggregated,
        ]);
    }

    /**
     * Return a 9x9 aggregated grid for the group
     */
    public function grid(Group $group)
    {
        // Positions 1..81
        $positions = range(1, 81);

        // eager-load each member's latestChart relation
        $members = $group->users()->with(['latestChart.chartEntries.album.artist'])->get();

        // For each position collect album occurrences
        $grid = [];

        foreach ($positions as $pos) {
            $counts = [];
            foreach ($members as $member) {
                $chart = $member->latestChart;
                if (!$chart) continue;
                $entry = $chart->chartEntries->where('position', $pos)->first();
                if (!$entry) continue;
                $albumId = $entry->album_id;
                if (!isset($counts[$albumId])) {
                    $counts[$albumId] = ['count' => 0, 'album' => $entry->album];
                }
                $counts[$albumId]['count']++;
            }

            if (empty($counts)) {
                $grid[] = null;
                continue;
            }

            // choose album with highest count, tie-breaker: lowest album id
            uasort($counts, function ($a, $b) {
                if ($a['count'] === $b['count']) return $a['album']->id <=> $b['album']->id;
                return $b['count'] <=> $a['count'];
            });

            $top = array_values($counts)[0];
            $album = $top['album'];

            $grid[] = [
                'position' => $pos,
                'album_id' => $album->id,
                'album_name' => $album->name,
                'artist_name' => $album->artist ? $album->artist->name : null,
                'count' => $top['count'],
            ];
        }

        // return as 9x9 matrix
        $matrix = array_chunk($grid, 9);

        // Build an aggregated chart structure compatible with ChartGrid component
        $chartEntries = [];
        $syntheticId = 1;
        foreach ($grid as $cell) {
            if ($cell === null) {
                $chartEntries[] = null;
                continue;
            }
            // find album model from members (lazy) -- try to fetch one member's album object
            $albumModel = null;
            foreach ($members as $m) {
                $c = $m->latestChart;
                if (!$c) continue;
                $entry = $c->chartEntries->where('album_id', $cell['album_id'])->first();
                if ($entry) {
                    $albumModel = $entry->album;
                    break;
                }
            }

            $chartEntries[] = [
                'id' => $syntheticId++,
                'position' => $cell['position'],
                'album' => [
                    'id' => $cell['album_id'],
                    'name' => $cell['album_name'],
                    'image_url' => $albumModel ? ($albumModel->image_url ?? null) : null,
                    'artist' => ['name' => $cell['artist_name']],
                ],
                'completed_album' => false,
            ];
        }

        $aggregatedChart = [
            'chart_type' => 'group_aggregate',
            'week_start_date' => now()->toDateString(),
            // preserve placeholders so frontend can render by position (81 entries)
            'chart_entries' => $chartEntries,
        ];

        return response()->json(['grid' => $matrix, 'aggregated_chart' => $aggregatedChart]);
    }

    /**
     * Show all groups the current user belongs to
     */
    public function index()
    {
        $user = auth()->user();
        $groups = $user->groups()->withCount('users')->get();

        return Inertia::render('Groups/Index', ['groups' => $groups]);
    }
}
