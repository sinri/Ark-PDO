<?php


namespace sinri\ark\database\exception;

use Exception;
use Throwable;

/**
 * Class ArkPDOExecutedWithEmptyResultSituation
 * @package sinri\ark\database\exception
 * @since 1.8.7
 * @since 1.8.10 not extends ArkPDOExecuteFetchFailedError but Exception.
 */
class ArkPDOExecutedWithEmptyResultSituation extends Exception
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
        $message = 'SQL queried but no row(s) fetched.';
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