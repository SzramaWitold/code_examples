<?php

namespace App\Services;

use App\Http\Requests\EventListRequest;
use App\Http\Requests\EventRequest;
use App\Mail\InviteMail;
use App\Models\Descriptors\EventDescriptor;
use App\Models\Event;
use App\Models\User;
use App\Repositories\Contracts\IEventRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class EventService
{
    private IEventRepository $repository;

    public function __construct(IEventRepository $repository)
    {
        $this->repository = $repository;
    }

    public function handleEventList(EventListRequest $request): LengthAwarePaginator
    {
        if (Auth::guard('api')->check()) {
            $user_id = Auth::guard('api')->user()->getAuthIdentifier();

            return $this->repository->authorizedList($request, $user_id);
        }

        return $this->repository->unauthorizedList($request);
    }

    public function addEvent(EventRequest $request): ?Event
    {
        if (Auth::guard('api')->check()) {
            $user_id = Auth::guard('api')->user()->getAuthIdentifier();

            return $this->repository->create($request, $user_id);
        }

        return null;
    }

    public function inviteUser(Event $event, User $user): ?Event
    {
        /** @var User $authUser */
        $authUser = Auth::user();
        if ($event->users()->wherePivot('user_id', $user->getAttribute('id'))->exists()) {
            return null;
        }
        if (Auth::check() && $event->isOwner($authUser)) {
            $event->users()->syncWithPivotValues($user, ['type' => EventDescriptor::INVITED], false);

            Mail::to($user->getAttribute('email'))->send(new InviteMail($event, $user));
            return $event;
        }

        return null;
    }

    public function removeUser(Event $event, User $user): ?Event
    {
        /** @var User $authUser */
        $authUser = Auth::user();
        if (Auth::check() && ($event->isOwner($authUser) || $user->getAttribute('id') == $authUser->getAttribute('id'))) {
            $event->users()->detach($user->getAttribute('id'));

            return $event;
        }

        return null;
    }

    public function join(Event $event): ?Event
    {
        if (!Auth::check()) {
            return null;
        }
        $user_id = Auth::guard('api')->user()->getAuthIdentifier();
        if ($event->canJoin($user_id)) {
            $event->users()->syncWithPivotValues($user_id,  ['type' => EventDescriptor::PARTICIPATE], false);

            return $event;
        }

        return null;
    }
}
