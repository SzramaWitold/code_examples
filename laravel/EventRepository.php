<?php

namespace App\Repositories;

use App\Http\Requests\EventListRequest;
use App\Http\Requests\EventRequest;
use App\Models\Descriptors\EventDescriptor;
use App\Models\Event;
use App\Models\Localization;
use App\Repositories\Contracts\IEventRepository;
use Illuminate\Support\Facades\DB;

class EventRepository implements IEventRepository
{
    private Event $event;
    private Localization $localization;

    public function __construct(Event $event, Localization $localization)
    {
        $this->event = $event;
        $this->localization = $localization;
    }

    public function authorizedList(EventListRequest $request, int $user_id)
    {
        return $this->event->filtersHandler($request->getFilters())
            ->with(['users', 'localization'])
            ->privateHandler($user_id)
            ->whereIn('id', $this->getEventsIdInLocationRange($request->getLocalization()))
            ->orderBy('register_end')
            ->paginate($request->getPerPage());
    }

    public function unauthorizedList(EventListRequest $request)
    {
        return $this->event->filtersHandler($request->getFilters())
            ->with(['users', 'localization'])
            ->publicOnly()
            ->whereIn('id', $this->getEventsIdInLocationRange($request->getLocalization()))
            ->orderBy('register_end')
            ->paginate($request->getPerPage());
    }

    public function create(EventRequest $request, int $user_id): Event
    {
        $event = $this->event->newInstance($request->getEventAttributes());
        $localization = $this->localization->newQuery()->firstOrCreate($request->getLocalizationAttributers(), $request->getLocalizationAttributers());
        $localization->save();
        $event->localization()->associate($localization);
        $event->save();
        $event->users()->attach($user_id, ['type' => EventDescriptor::OWNER]);

        return $event;
    }

    /**
     * @param array<float> $localization
     * @param float $range
     * @return array<integer>
     */
    private function getEventsIdInLocationRange(array $localization, float $range = 5): array
    {
        $return = DB::select(
            'SELECT id FROM
                    (SELECT events.id, (3959 * acos(cos(radians(' . $localization['latitude'] . ')) * cos(radians(l.latitude)) *
                    cos(radians(l.longitude) - radians(' . $localization['longitude'] . ')) +
                    sin(radians(' . $localization['latitude'] . ')) * sin(radians(l.latitude))))
                    AS distance
                    FROM events
                    LEFT JOIN localizations l on events.localization_id = l.id
                    ) AS distances
                WHERE distance < ' . $range . '
                ORDER BY distance
            ');

        return array_map(function ($item) {
            return $item->id;
        }, $return);
    }
}
