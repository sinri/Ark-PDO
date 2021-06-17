<?php


namespace sinri\ark\database\exception;

use Exception;

/**
 * Class ArkPDOExecuteFetchFailedError
 * @package sinri\ark\database\exception
 * @since 1.8.0
 *
 * When PDO Statement `fetch` returns `false`,
 * such as,
 * no result row when get row;
 *
 * @deprecated since 1.8.10, use ArkPDOExecutedWithEmptyResultSituation directly.
 */
class ArkPDOExecuteFetchFailedError extends Exception
{

}