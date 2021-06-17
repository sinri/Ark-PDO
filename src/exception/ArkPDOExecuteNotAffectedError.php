<?php


namespace sinri\ark\database\exception;

use Exception;
use Throwable;

/**
 * Class ArkPDOExecuteNotAffectedError
 * @package sinri\ark\database\exception
 * @since 1.8.0
 *
 * When PDO `exec` returns `0`
 * Such as,
 *  Update or delete without any modification actually;
 *  Ignore used in insert into.
 */
class ArkPDOExecuteNotAffectedError extends Exception
{
    /**
     * @var string
     */
    protected $relatedSQL;

    /**
     * @return string
     */
    public function getRelatedSQL()
    {
        return $this->relatedSQL;
    }

    public function __construct($relatedSQL = '', $code = 0, Throwable $previous = null)
    {
        $message = 'SQL queried but no row(s) affected.';
        $this->relatedSQL = $relatedSQL;
        if (!empty($relatedSQL)) {
            $relatedSQL = " SQL: " . $relatedSQL;
        }

        parent::__construct(
            $message . $relatedSQL,
            $code,
            $previous
        );
    }
}