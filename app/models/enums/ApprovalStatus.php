<?php

namespace Models\enums;

enum ApprovalStatus
{
    case PENDING;
    case APPROVED;
    case REJECTED;
}