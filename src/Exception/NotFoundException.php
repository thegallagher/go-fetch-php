<?php

namespace TheGallagher\GoFetch\Exception;

/**
 * HTTP Not Found (404) Exception
 */
class NotFoundException extends RequestException
{
    /**
     * NotFoundException constructor.
     *
     * @param string $message
     * @param \Exception|null $previous
     */
    public function __construct($message = 'Not Found', \Exception $previous = null)
    {
        parent::__construct($message, 404, $previous);
    }
}