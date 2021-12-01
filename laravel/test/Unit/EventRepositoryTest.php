<?php

namespace Tests\Unit\Repositories\EventRepository\AuthorizedList;

use App\Http\Requests\EventListRequest;
use App\Models\Descriptors\EventDescriptor;
use App\Models\Event;
use App\Models\Localization;
use App\Repositories\EventRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\ApiTestCase;
use Mockery;
use Mockery\LegacyMockInterface;

class EventRepositoryTest extends ApiTestCase
{
    use DatabaseTransactions;

    private EventRepository $service;
    private EventListRequest | LegacyMockInterface $requestMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = $this->app->make(EventRepository::class);
        $this->requestMock = Mockery::mock(EventListRequest::class);
        $this->requestMock->shouldReceive('getPerPage')->andReturn(1);
        $this->requestMock->shouldReceive('getPage')->andReturn(1);
    }

    /**
     * @test
     */
    public function authorizedList_getEmptyResults()
    {
        $event = $this->prepareEvent(EventDescriptor::PRIVATE);
        $this->requestMock->shouldReceive('getFilters')->andReturn([]);
        $this->requestMock->shouldReceive('getLocalization')->andReturn([
            'longitude' => $event->localization->longitude,
            'latitude' => $event->localization->latitude,
        ]);
        $user = $this->createAndBeUser();
        $return = $this->service->authorizedList($this->requestMock, $user->getAuthIdentifier());
        $this->assertTrue(count($return->items()) === 0);
    }

    /**
     * @test
     */
    public function authorizedList_getPublicResults()
    {
        $event = $this->prepareEvent(EventDescriptor::PUBLIC);

        $this->requestMock->shouldReceive('getFilters')->andReturn([]);
        $this->requestMock->shouldReceive('getLocalization')->andReturn([
            'longitude' => $event->localization->longitude,
            'latitude' => $event->localization->latitude,
        ]);
        $user = $this->createAndBeUser();
        /** @var LengthAwarePaginator $return */
        $return = $this->service->authorizedList($this->requestMock, $user->getAuthIdentifier());
        $this->assertTrue(count($return->items()) > 0);
    }

    /**
     * @test
     */
    public function authorizedList_getPrivateResults()
    {
        $user = $this->createAndBeUser();
        $event = $this->prepareEvent(EventDescriptor::PRIVATE);
        $event->users()->attach($user->getAuthIdentifier(), ['type' => EventDescriptor::INVITED]);
        $this->requestMock->shouldReceive('getFilters')->andReturn([]);
        $this->requestMock->shouldReceive('getLocalization')->andReturn([
            'longitude' => $event->localization->longitude,
            'latitude' => $event->localization->latitude,
        ]);

        /** @var LengthAwarePaginator $return */
        $return = $this->service->authorizedList($this->requestMock, $user->getAuthIdentifier());
        $this->assertTrue(count($return->items()) > 0);
    }

    private function prepareEvent(string $type)
    {
        /** @var Event $event */
        $event = Event::factory()->create(['type' => $type]);

        /** @var Localization $localization */
        $localization = Localization::factory()->create([
            'latitude' => 0,
            'longitude' => 0
        ]);
        $event->localization()->associate($localization);
        $event->save();

        return $event;
    }
}
