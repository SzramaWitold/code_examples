<?php

namespace Tests\Feature\Http\Controllers\EventController\AddUser;

use App\Mail\InviteMail;
use App\Models\Descriptors\EventDescriptor;
use App\Models\Event;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\ApiTestCase;

class EventControllerAddUserTest extends ApiTestCase
{
    use DatabaseTransactions, TestTrait;

    private Carbon $time;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->time = Carbon::now('Europe/Warsaw')->addDecade();
    }

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    /**
     * @test
     */
    public function addUser_notAuthorized()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create();

        $response = $this->put(route('event.user.add', [ 'event' => $event->getAttribute('id'), 'user' => $user->getAttribute('id')]));

        $response->assertStatus(401);
    }

    /**
     * @test
     */
    public function addUser_userNotOwner()
    {
        $this->createAndBeUser();
        $user = User::factory()->create();
        $event = Event::factory()->create();

        $response = $this->put(route('event.user.add', [ 'event' => $event->getAttribute('id'), 'user' => $user->getAttribute('id')]));

        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function addUser_userEmailSend()
    {
        $owner = $this->createAndBeUser();
        $user = User::factory()->create();
        /** @var Event $event */
        $event = Event::factory()->create();
        $event->users()->attach($owner, ['type' => EventDescriptor::OWNER]);
        $response = $this->put(route('event.user.add', [ 'event' => $event->getAttribute('id'), 'user' => $user->getAttribute('id')]));
        $response->assertOk();

        Mail::assertSent(InviteMail::class);
    }
}
