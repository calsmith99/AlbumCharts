<?php

namespace Tests\Unit;

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_group_has_owner_and_users_relation()
    {
        $owner = User::factory()->create();
        $group = Group::create(['name' => 'Test Group', 'owner_id' => $owner->id]);

        $this->assertEquals($owner->id, $group->owner->id);

        $group->users()->attach($owner->id);
        $this->assertTrue($group->users->contains('id', $owner->id));
    }
}
