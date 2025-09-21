<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\Artist;
use App\Models\Chart;
use App\Models\ChartEntry;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupGridTest extends TestCase
{
    use RefreshDatabase;

    public function test_grid_aggregates_members_latest_charts()
    {
        // create group owner and member
        $owner = User::factory()->create();
        $member = User::factory()->create();

        $group = Group::create(['name' => 'Grid Group', 'owner_id' => $owner->id]);
        $group->users()->attach([$owner->id, $member->id]);

        // create artist and albums
        $artist = Artist::create(['name' => 'Artist']);
        $albumA = Album::create(['artist_id' => $artist->id, 'name' => 'Album A']);
        $albumB = Album::create(['artist_id' => $artist->id, 'name' => 'Album B']);

        // owner chart with albumA in position 1
        $chart1 = Chart::create(['user_id' => $owner->id, 'week_start_date' => now(), 'chart_type' => 'album', 'chart_size' => 100]);
        ChartEntry::create(['chart_id' => $chart1->id, 'album_id' => $albumA->id, 'position' => 1, 'play_count' => 10]);

        // member chart with albumA in position 1 and albumB in position 2
        $chart2 = Chart::create(['user_id' => $member->id, 'week_start_date' => now(), 'chart_type' => 'album', 'chart_size' => 100]);
        ChartEntry::create(['chart_id' => $chart2->id, 'album_id' => $albumA->id, 'position' => 1, 'play_count' => 8]);
        ChartEntry::create(['chart_id' => $chart2->id, 'album_id' => $albumB->id, 'position' => 2, 'play_count' => 7]);

        // call grid endpoint
        $response = $this->actingAs($owner)->getJson(route('groups.grid', $group->id));
        $response->assertStatus(200);

        $data = $response->json('grid');
        $this->assertIsArray($data);

        // position 1 should pick albumA with count 2
        $pos1 = $data[0][0];
        $this->assertEquals(1, $pos1['position']);
        $this->assertEquals($albumA->id, $pos1['album_id']);
        $this->assertEquals(2, $pos1['count']);

        // position 2 should pick albumB with count 1
        $pos2 = $data[0][1];
        $this->assertEquals(2, $pos2['position']);
        $this->assertEquals($albumB->id, $pos2['album_id']);
        $this->assertEquals(1, $pos2['count']);
    }
}
