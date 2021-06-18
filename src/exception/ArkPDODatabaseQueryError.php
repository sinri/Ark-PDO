<?php


namespace sinri\ark\database\exception;


use RuntimeException;
use Throwable;

/**
 * Class DatabaseOperationError
 * @package sinri\ark\database\Exception
 * @since 2.0.13
 * @since 2.0.25 changed construction
 * @since 2.0.26 Changed to extends RuntimeException
 *
 * When any operations on database meet error
 * The message is assumed as PDO ERROR DESCRIPTION
 */
class ArkPDODatabaseQueryError extends RuntimeException
{
    /**
     * @var string
     */
    protected $relatedSQL;
    /**
     * @var string
     */
    protected $pdoError;

    /**
     * @return string
     */
    public function getPdoError(): string
    {
        return $this->pdoError;
    }

    /**
     * @return string
     */
    public function getRelatedSQL(): string
    {
        return $this->relatedSQL;
    }

    public function __construct(string $sql, string $pdoError, $code = 0, Throwable $previous = null)
    {
        $message = "Failed to query SQL";
        if (!empty($sql)) {
            $message .= ': ' . $sql;
        }
        if (!empty($pdoError)) {
            $message .= "; PDO Error: " . $pdoError;
        }

        parent::__construct($message, $code, $previous);
        $this->relatedSQL = $sql;
        $this->pdoError = $pdoError;
    }
}