<?php


namespace sinri\ark\database\exception;

use Exception;

/**
 * Class ArkPDOExecuteNotAffectedError
 * @package sinri\ark\database\exception
 * @since 1.8.0
 *
 * When PDO `exec` returns `0`
 */
class ArkPDOExecuteNotAffectedError extends Exception
{

}