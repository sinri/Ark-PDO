<?php


namespace sinri\ark\database\exception;


use Exception;

/**
 * Class ArkPDOQueryResultFinishedStreamingSituation
 * @package sinri\ark\database\exception
 * @since 2.0.23
 *
 * When streaming result, find there hed been no more rows
 */
class ArkPDOQueryResultFinishedStreamingSituation extends Exception
{

}