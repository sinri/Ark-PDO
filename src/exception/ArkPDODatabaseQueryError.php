<?php


namespace sinri\ark\database\exception;


use Exception;

/**
 * Class DatabaseOperationError
 * @package sinri\ark\database\Exception
 * @since 2.0.13
 *
 * When any operations on database meet error
 * The code is assumed as PDO ERROR CODE
 * The message is assumed as PDO ERROR DESCRIPTION
 */
class ArkPDODatabaseQueryError extends Exception
{

}