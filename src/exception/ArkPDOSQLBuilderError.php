<?php


namespace sinri\ark\database\exception;


use RuntimeException;
use Throwable;

/**
 * Class ArkPDOSQLBuilderError
 * @package sinri\ark\database\Exception
 * @since 2.0.13
 * @since 2.0.23 becomes subclass of RuntimeException
 *
 * Before send to database, when the SQL string is in local building progress, something wrong found.
 *
 * It could not be fixed by code.
 */
class ArkPDOSQLBuilderError extends RuntimeException
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