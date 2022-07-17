<?php

namespace App\Enums;

use App\Traits\EnumValues;

enum Roles:string{
    use EnumValues;

    case Admin = 'admin';
    case Moderator = 'moderator';
    case Writer = 'writer';
    case Journalist = 'journalist';


}
