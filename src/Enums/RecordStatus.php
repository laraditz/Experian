<?php

namespace Laraditz\Experian\Enums;

enum RecordStatus: int
{
    case Pending        = 1;
    case Processing     = 2;
    case Completed      = 3;
    case Failed         = 4;
}
