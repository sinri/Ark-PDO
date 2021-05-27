<?php


namespace sinri\ark\database\exception;


use Exception;

/**
 * Class ArkPDOExecuteFailedError
 * @package sinri\ark\database\exception
 * @since 1.8.0
 *
 * When PDO `exec` or PDO Statement `execute` returns `false`
 */
class ArkPDOExecuteFailedError extends Exception
{

}