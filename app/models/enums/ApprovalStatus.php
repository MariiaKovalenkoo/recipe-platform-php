<?php

namespace Models\enums;

enum ApprovalStatus: string
{
    case PRIVATE = 'Private';
    case PENDING = 'Pending';
    case APPROVED = 'Approved';
    case REJECTED = 'Rejected';
}