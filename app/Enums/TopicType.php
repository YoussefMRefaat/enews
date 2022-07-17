<?php

namespace App\Enums ;

use App\Traits\EnumValues;

enum TopicType: string{
    use EnumValues;

    case News = 'news';
    case Article = 'article';

}
