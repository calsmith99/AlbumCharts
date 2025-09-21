<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupInvite;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GroupInviteController extends Controller
{
    public function create(Request $request, Group $group)
    {
        // TODO: add authorization (owner-only) in a policy
        $invite = GroupInvite::create([
            'group_id' => $group->id,
            'token' => Str::random(32),
            'created_by' => $request->user()->id,
            'expires_at' => now()->addDays(7),
        ]);

        return response()->json(['token' => $invite->token]);
    }

    public function accept(Request $request, $token)
    {
        $invite = GroupInvite::where('token', $token)->firstOrFail();
        // Ensure expires_at is a Carbon instance before checking
        $expires = $invite->expires_at ? \Illuminate\Support\Carbon::parse($invite->expires_at) : null;
        if ($expires && $expires->isPast()) {
            abort(410, 'Invite expired');
        }

        $group = $invite->group;
        $group->users()->syncWithoutDetaching([$request->user()->id]);

        return redirect()->route('groups.show', $group->id);
    }
}
