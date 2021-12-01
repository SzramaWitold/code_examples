<?php

namespace App\Models\Descriptors;

class EventDescriptor
{
    const PUBLIC = 'public';
    const PRIVATE = 'private';

    const START = 'start';
    const END = 'end';
    const REGISTER_END = 'register_end';

    const INVITED = 'invited';
    const OWNER = 'owner';
    const PARTICIPATE = 'participate';

    const PARTICIPANT_TYPES = [
        self::OWNER,
        self::PARTICIPATE
    ];

    const STATUS = [
        self::PRIVATE,
        self::PUBLIC
    ];

    const FILTER_DATES = [
        self::START,
        self::END,
        self::REGISTER_END,
    ];
}
