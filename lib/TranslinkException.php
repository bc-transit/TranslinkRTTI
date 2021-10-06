<?php namespace translinkrtti\lib;

/**
 * Translink Exception
 * 
 * Encapsulates returned errors from the API into an exception class for easy testing against.
 * 
 * @author Martyr2
 * @copyright 2021 Martyr2
 * @link https://www.coderslexicon.com
 * 
 */

class TranslinkException extends \Exception 
{
    public function __construct($message = '', $code = 0, \Throwable $previous = null) 
    {
        parent::__construct($message, $code, $previous);
    }
}
