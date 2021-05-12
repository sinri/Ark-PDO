<?php


namespace sinri\ark\database\exception;


use Exception;
use Throwable;

class ArkPDORollbackSituation extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}