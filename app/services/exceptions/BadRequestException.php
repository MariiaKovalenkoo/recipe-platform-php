<?php
namespace Services\exceptions;

class BadRequestException extends \Exception
{
    public function __construct(string $message = "The request was invalid.", int $code = 400, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}