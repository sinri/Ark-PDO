<?php


namespace sinri\ark\database\exception;


use Exception;
use Throwable;

/**
 * Class ArkPDORollbackSituation
 * @package sinri\ark\database\exception
 * @since 2.0.20
 * @since 2.0.25 changed construction
 */
class ArkPDORollbackSituation extends Exception
{
    public function __construct(Throwable $previous)
    {
        $message = 'Transaction Rollback. Caused by ' . get_class($previous);
        if (!empty($previous->getMessage())) {
            $message .= ': ' . $previous->getMessage();
        } else {
            $message .= '.';
        }

        parent::__construct($message, 0, $previous);
    }
}