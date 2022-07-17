<?php

namespace App\Enums;

use App\Traits\EnumValues;

enum SubscribedType: string{
    use EnumValues;

    case Topic = 'topic';
    case Clerk = 'clerk';
    case Tag = 'tag';

}
