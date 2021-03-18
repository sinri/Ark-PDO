<?php


namespace sinri\ark\database\Exception;


use Exception;
use Throwable;

/**
 * Class ArkPDOSQLBuilderError
 * @package sinri\ark\database\Exception
 * @since 2.0.13
 *
 * Before send to database, when the SQL string is in local building progress, something wrong found.
 */
class ArkPDOSQLBuilderError extends Exception
{
    /**
     * @var string
     */
    protected $wrongSQLPiece;

    public function __construct($message = "", $wrongSQLPiece = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->wrongSQLPiece = $wrongSQLPiece;
    }

    /**
     * @return string
     */
    public function getWrongSQLPiece(): string
    {
        return $this->wrongSQLPiece;
    }

    /**
     * @param string $wrongSQLPiece
     * @return ArkPDOSQLBuilderError
     */
    public function setWrongSQLPiece(string $wrongSQLPiece): ArkPDOSQLBuilderError
    {
        $this->wrongSQLPiece = $wrongSQLPiece;
        return $this;
    }


}