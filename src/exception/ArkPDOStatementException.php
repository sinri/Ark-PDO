<?php


namespace sinri\ark\database\exception;


use RuntimeException;
use Throwable;

/**
 * Class ArkPDOStatementException
 * @package sinri\ark\database\Exception
 * @since 2.0.13
 * @since 2.0.23 becomes subclass of RuntimeException
 *
 * When a SQL cannot be made into a PDO statement
 */
class ArkPDOStatementException extends RuntimeException
{
    protected $sql;

    public function __construct($message = "", $sql = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->sql = $sql;
    }

    /**
     * @return mixed|string
     */
    public function getSql(): string
    {
        return $this->sql;
    }

    /**
     * @param mixed|string $sql
     * @return ArkPDOStatementException
     */
    public function setSql(string $sql): ArkPDOStatementException
    {
        $this->sql = $sql;
        return $this;
    }
}