<?php

namespace Services\exceptions;

class AccessDeniedException extends \Exception
{
    public function __construct(string $message = "You do not have permission to access this resource.", int $code = 403, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}