<?php

namespace TheGallagher\GoFetch\Exception;

/**
 * HTTP Unprocessable Entity (422) Exception
 */
class UnprocessableEntityException extends RequestException
{
    /**
     * UnprocessableEntityException constructor.
     *
     * @param string $message
     * @param \Exception|null $previous
     */
    public function __construct($message = 'Unprocessable Entity', \Exception $previous = null)
    {
        parent::__construct($message, 422, $previous);
    }
}