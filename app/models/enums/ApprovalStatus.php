<?php

namespace Models\enums;

enum ApprovalStatus: string
{
    case APPROVED = 'Approved';
    case PRIVATE = 'Private';
    case PENDING = 'Pending';
    case REJECTED = 'Rejected';
}