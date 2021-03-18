<?php


namespace sinri\ark\database\Exception;


use Exception;
use Throwable;

/**
 * Class QueryResultIsNotQueriedError
 * @package sinri\ark\database\Exception
 * @since 2.0.13
 *
 * When ArkDatabaseQueryResult hold non-QUERIED status after querying.
 */
class ArkPDOQueryResultIsNotQueriedError extends Exception
{
    protected $action;
    protected $status;
    protected $databaseError;

    public function __construct($action, $status, $databaseError, $code = 0, Throwable $previous = null)
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
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param mixed $action
     * @return ArkPDOQueryResultIsNotQueriedError
     */
    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     * @return ArkPDOQueryResultIsNotQueriedError
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDatabaseError()
    {
        return $this->databaseError;
    }

    /**
     * @param mixed $databaseError
     * @return ArkPDOQueryResultIsNotQueriedError
     */
    public function setDatabaseError($databaseError)
    {
        $this->databaseError = $databaseError;
        return $this;
    }
}