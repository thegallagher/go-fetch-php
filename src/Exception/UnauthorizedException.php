<?php

namespace TheGallagher\GoFetch\Exception;

/**
 * HTTP Unauthorized (401) Exception
 */
class UnauthorizedException extends RequestException
{
    /**
     * UnauthorizedException constructor.
     *
     * @param string $message
     * @param \Exception|null $previous
     */
    public function __construct($message = 'Unauthorized', \Exception $previous = null)
    {
        parent::__construct($message, 401, $previous);
    }
}