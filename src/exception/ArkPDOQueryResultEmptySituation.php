<?php


namespace sinri\ark\database\exception;


use Exception;
use Throwable;

/**
 * Class ArkPDOQueryResultEmptySituation
 * @package sinri\ark\database\exception
 * @since 2.0.23
 * @since 2.0.25 changed construction
 *
 * When the query result contains zero rows.
 */
class ArkPDOQueryResultEmptySituation extends Exception
{
    /**
     * @var string
     */
    protected $relatedSQL;

    /**
     * @return string
     */
    public function getRelatedSQL(): string
    {
        return $this->relatedSQL;
    }

    public function __construct(string $sql, $code = 0, Throwable $previous = null)
    {
        $message = 'SQL query result is empty: ' . $sql;
        parent::__construct($message, $code, $previous);
        $this->relatedSQL = $sql;
    }
}