<?php

namespace Tests\Feature;

use App\Activity;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ActivityTest extends TestCase
{

    use DatabaseMigrations;
    
    /** @test */
    public function it_records_activities_when_a_thread_is_created()
    {

        $this->signIn();

        $thread = create('App\Thread');

        $list = ['type' => 'created_thread',
            'user_id' => auth()->id(),
            'subject_id' => $thread->id,
            'subject_type' => 'App\Thread'];

        $this->assertDatabaseHas('activities', $list);

        $activity = Activity::first();

//        $activity->assertEquals($activity->subject->id, $thread->id);
        $this->assertEquals($activity->subject->id, $thread->id);
    }

    /** @test */
    function it_records_activity_when_a_reply_is_created()
    {
        $this->signIn();

        $reply = create('App\Reply'); // will automatically create a thread

        $this->assertEquals(2, Activity::count());

    }

    /** @test */
    function it_fetches_a_feed_for_any_user()
    {
        $this->signIn(); // assumed signed in
        create('App\Thread', ['user_id' => auth()->id()], 2); // create two threads with this user
        auth()->user()->activity()->first()->update(['created_at' => Carbon::now()->subWeek()]);
        $feed = Activity::feed(auth()->user(), 50);

        $this->assertTrue($feed->keys()->contains(
            Carbon::now()->format('Y-m-d')
        ));

        $this->assertTrue($feed->keys()->contains(
            Carbon::now()->subWeek()->format('Y-m-d')
        ));
    }

}