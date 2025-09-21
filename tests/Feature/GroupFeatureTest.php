<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_group()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('groups.store'), [
            'name' => 'My Test Group',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('groups', ['name' => 'My Test Group', 'owner_id' => $user->id]);
    }

    public function test_invite_can_be_created_and_accepted()
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();

        $this->actingAs($owner);
        $response = $this->post(route('groups.store'), ['name' => 'Invite Group']);
        $group = Group::where('name', 'Invite Group')->first();

        // create invite
        $createInvite = $this->actingAs($owner)->postJson(route('groups.invites.create', $group->id));
        $createInvite->assertStatus(200)->assertJsonStructure(['token']);
        $token = $createInvite->json('token');

        // accept invite as another user
        $accept = $this->actingAs($member)->get(route('groups.invites.accept', $token));
        $accept->assertRedirect(route('groups.show', $group->id));

        $this->assertDatabaseHas('group_user', ['group_id' => $group->id, 'user_id' => $member->id]);
    }
}
