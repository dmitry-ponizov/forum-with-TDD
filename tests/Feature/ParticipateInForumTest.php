<?php

namespace Tests\Feature;


use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Inspections\Spam;

class ParticipateInForumTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * A basic test example.
     *
     * @return void
     */


    function test_unauthenticated_users_may_not_add_replies()
    {
        $this->expectException('Illuminate\Auth\AuthenticationException');

        $this->post('/threads/some-channel/1/replies', [])

            ->assertRedirect('/login ');
    }

    public function test_an_authenticate_user_may_participate_in_forum_threads()
    {

        $this->signIn();

        $thread = factory('App\Thread')->create();

        $reply = factory('App\Reply')->make();

        $this->post($thread->path() . '/replies', $reply->toArray());

        $this->assertDatabaseHas('replies',['body'=>$reply->body]);

        $this->assertEquals(1,$thread->fresh()->replies_count);
    }

    public function test_a_reply_requires_a_body()

    {
        $this->withExceptionHandling()->signIn();

        $thread = factory('App\Thread')->create();


        $reply = factory('App\Reply', ['body' => null])->make();

        $this->post($thread->path() . '/replies', $reply->toArray())
            ->assertSessionHasErrors('body');
    }

    public function test_unauthorized_user_can_not_delete_thread()
    {
        $this->withExceptionHandling();

        $reply = create('App\Reply');

        $this->delete("/replies/{$reply->id}")
            ->assertRedirect('login');

        $this->signIn()
            ->delete("/replies/{$reply->id}")
            ->assertStatus(403);
    }

    function test_authorized_users_can_delete_replies()
    {
        $this->signIn();

        $reply = create('App\Reply', ['user_id' => auth()->id()]);

        $this->delete("/replies/{$reply->id}");

        $this->assertDatabaseMissing('replies', ['id' => $reply->id]);

        $this->assertEquals(0,$reply->thread->fresh()->replies_count);
    }

    function test_authorized_user_can_update_replies()
    {
        $this->signIn();

        $reply = create('App\Reply', ['user_id' => auth()->id()]);

        $updatedReply = 'You, been changed fool!';

        $this->patch("/replies/{$reply->id}",['body'=> $updatedReply]);

        $this->assertDatabaseHas('replies',['id'=>$reply->id,'body'=>$updatedReply]);

    }

    function test_unauthorized_users_cannot_update_replies()
    {
        $this->withExceptionHandling();

        $reply = create('App\Reply');

        $this->patch("/replies/{$reply->id}")
            ->assertRedirect('login');

        $this->signIn()
            ->patch("/replies/{$reply->id}")
            ->assertStatus(200);
    }

    public function test_replies_that_contain_spam_may_not_be_created()
    {
        $this->signIn();

        $thread = create('App\Thread');

        $reply = make('App\Reply',[
            'body' => 'Yahoo customer support'
        ]);

        $this->expectException(\Exception::class);

        $this->post($thread->path() . '/replies',$reply->toArray());

    }

}
