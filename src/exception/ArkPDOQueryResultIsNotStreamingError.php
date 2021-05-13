<?php


namespace sinri\ark\database\exception;


use Exception;
use Throwable;

/**
 * Class ArkPDOQueryResultIsNotStreamingError
 * @package sinri\ark\database\exception
 * @since 2.0.21
 *
 * When ArkDatabaseQueryResult hold non-STREAMING status after querying.
 */
class ArkPDOQueryResultIsNotStreamingError extends Exception
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
     * ArkPDOQueryResultIsNotStreamingError constructor.
     * @param string $action
     * @param string $status
     * @param string $databaseError
     * @param string $sql
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($action, $status, $databaseError, $sql = '', $code = 0, Throwable $previous = null)
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
     * @return ArkPDOQueryResultIsNotStreamingError
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
     * @return ArkPDOQueryResultIsNotStreamingError
     */
    public function setAction($action): ArkPDOQueryResultIsNotStreamingError
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
     * @return ArkPDOQueryResultIsNotStreamingError
     */
    public function setStatus($status): ArkPDOQueryResultIsNotStreamingError
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
     * @return ArkPDOQueryResultIsNotStreamingError
     */
    public function setDatabaseError($databaseError): ArkPDOQueryResultIsNotStreamingError
    {
        $this->databaseError = $databaseError;
        return $this;
    }
}