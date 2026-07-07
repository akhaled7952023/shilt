<?php

namespace App\Enums;

enum PeriodAction: string
{
    case Opened        = 'opened';
    case EditingStarted = 'editing_started';
    case Approved      = 'approved';
    case Published     = 'published';
    case Closed        = 'closed';
    case Reopened      = 'reopened';
}
