<?php

namespace Services\exceptions;

class UnauthorizedException extends \Exception
{
    public function __construct(string $message = "Authentication is required to access this resource.", int $code = 401, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}