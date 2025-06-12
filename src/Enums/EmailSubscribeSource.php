<?php

namespace Brucelwayne\Subscribe\Enums;

enum EmailSubscribeSource: int
{
    case UNKNOWN = 0;
    case PopupSubscribe = 1;
    case Footer = 2;
}
