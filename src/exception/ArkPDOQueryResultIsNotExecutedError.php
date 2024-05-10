<?php


namespace sinri\ark\database\exception;


use RuntimeException;
use Throwable;

/**
 * Class ArkPDOQueryResultIsNotExecutedError
 * @package sinri\ark\database\exception
 * @since 2.0.18
 * @since 2.0.26 Changed to extends RuntimeException
 */
class ArkPDOQueryResultIsNotExecutedError extends RuntimeException
{
    /**
     * @var string
     */
    protected string $action;
    /**
     * @var string
     */
    protected string $status;
    /**
     * @var string
     */
    protected string $databaseError;
    /**
     * @var string
     * @since 2.0.18
     */
    protected string $sql;

    /**
     * ArkPDOQueryResultIsNotExecutedError constructor.
     * @param string $action
     * @param string $status
     * @param string $databaseError
     * @param string $sql
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $action, string $status, string $databaseError, string $sql = '', int $code = 0, Throwable $previous = null)
    {
        parent::__construct(
            "Action Failed: " . $action . " | "
            . "Current Status is " . $status . " | "
            . "Database Error: " . $databaseError,
            $code,
            $previous
        );

        $this->action = $action;
        $this->status = $status;
        $this->databaseError = $databaseError;
        $this->sql = $sql;
    }

    /**
     * @return string
     */
    public function getSql(): string
    {
        return $this->sql;
    }

    /**
     * @param string $sql
     * @return ArkPDOQueryResultIsNotExecutedError
     */
    public function setSql(string $sql): static
    {
        $this->sql = $sql;
        return $this;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @param string $action
     * @return ArkPDOQueryResultIsNotExecutedError
     */
    public function setAction(string $action): ArkPDOQueryResultIsNotExecutedError
    {
        $this->action = $action;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return ArkPDOQueryResultIsNotExecutedError
     */
    public function setStatus(string $status): ArkPDOQueryResultIsNotExecutedError
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getDatabaseError(): string
    {
        return $this->databaseError;
    }

    /**
     * @param string $databaseError
     * @return ArkPDOQueryResultIsNotExecutedError
     */
    public function setDatabaseError(string $databaseError): ArkPDOQueryResultIsNotExecutedError
    {
        $this->databaseError = $databaseError;
        return $this;
    }

}