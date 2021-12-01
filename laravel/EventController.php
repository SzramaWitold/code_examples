<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventRequest;
use App\Http\Requests\EventListRequest;
use App\Http\Resources\EventResource;
use App\Services\EventService;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group Event
 */
class EventController extends Controller
{
    /**
     * Events List
     *
     * Received events list based on authentication and current localization
     *
     * @responseFile storage/responses/events.list.json
     *
     * @param EventListRequest $request
     * @param EventService $service
     * @return ResourceCollection<EventResource>
     */
    public function index(EventListRequest $request, EventService $service): ResourceCollection
    {
        $events = $service->handleEventList($request);

        return EventResource::collection($events);
    }

    /**
     * Add event
     *
     * Add new event
     *
     * @apiResource App\Http\Resources\EventResource
     * @apiResourceModel App\Models\Event
     *
     * @param EventRequest $request
     * @param EventService $service
     * @return EventResource
     */
    public function store(EventRequest $request, EventService $service): EventResource
    {
        return new EventResource($service->addEvent($request));
    }

    /**
     * Event details
     *
     * Information about item details
     *
     * @apiResource App\Http\Resources\EventResource
     * @apiResourceModel App\Models\Event
     *
     * @param Event $event
     * @return EventResource
     */
    public function show(Event $event): EventResource
    {
        return new EventResource($event);
    }

    /**
     * Invite user
     *
     * Invite user to your event
     *
     * @apiResource 201 App\Http\Resources\EventResource
     * @apiResourceModel App\Models\Event
     *
     * @response 403 {
     *  "data": "No permission for add user"
     * }
     *
     * @param Event $event
     * @param User $user
     * @param EventService $service
     * @return EventResource|JsonResponse
     */
    public function inviteUser(Event $event, User $user, EventService $service): EventResource | JsonResponse
    {
        $event = $service->inviteUser($event, $user);

        if ($event) {
            return new EventResource($event);
        }
        return new JsonResponse('No permission for add user', Response::HTTP_FORBIDDEN);
    }

    /**
     * Join Event
     *
     * join to public event or user is invited
     *
     * @apiResource 201 App\Http\Resources\EventResource
     * @apiResourceModel App\Models\Event
     *
     * @response 403 {
     *  "data": "No permission to join"
     * }
     *
     * @param Event $event
     * @param EventService $service
     * @return EventResource|JsonResponse
     */
    public function join(Event $event, EventService $service): EventResource | JsonResponse
    {
        $event = $service->join($event);

         if ($event) {
             return new EventResource($event);
         }

        return new JsonResponse('No permission to join', Response::HTTP_FORBIDDEN);
    }

    /**
     *  Remove User
     *
     * Remove user from the event
     *
     *
     * @apiResource 201 App\Http\Resources\EventResource
     * @apiResourceModel App\Models\Event
     *
     * @response 403 {
     *  "data": "No permission for remove user"
     * }
     * @param Event $event
     * @param User $user
     * @param EventService $service
     * @return EventResource|JsonResponse
     */
    public function removeUser(Event $event, User $user, EventService $service): EventResource | JsonResponse
    {
        $event = $service->removeUser($event, $user);

        if ($event) {
            return new EventResource($event);
        }

        return new JsonResponse('No permission for remove user', Response::HTTP_FORBIDDEN);
    }
}
