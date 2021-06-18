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
    protected $action;
    /**
     * @var string
     */
    protected $status;
    /**
     * @var string
     */
    protected $databaseError;
    /**
     * @var string
     * @since 2.0.18
     */
    protected $sql;

    /**
     * ArkPDOQueryResultIsNotExecutedError constructor.
     * @param string $action
     * @param string $status
     * @param string $databaseError
     * @param string $sql
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $action, string $status, string $databaseError, string $sql = '', $code = 0, Throwable $previous = null)
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
    public function getSql()
    {
        return $this->sql;
    }

    /**
     * @param string $sql
     * @return ArkPDOQueryResultIsNotExecutedError
     */
    public function setSql($sql)
    {
        $this->sql = $sql;
        return $this;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param string $action
     * @return ArkPDOQueryResultIsNotExecutedError
     */
    public function setAction($action): ArkPDOQueryResultIsNotExecutedError
    {
        $this->action = $action;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return ArkPDOQueryResultIsNotExecutedError
     */
    public function setStatus($status): ArkPDOQueryResultIsNotExecutedError
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getDatabaseError()
    {
        return $this->databaseError;
    }

    /**
     * @param string $databaseError
     * @return ArkPDOQueryResultIsNotExecutedError
     */
    public function setDatabaseError($databaseError): ArkPDOQueryResultIsNotExecutedError
    {
        $this->databaseError = $databaseError;
        return $this;
    }

}