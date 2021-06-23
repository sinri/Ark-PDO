<?php


namespace sinri\ark\database\exception;


use Exception;
use Throwable;

/**
 * Class ArkPDOExecuteFailedError
 * @package sinri\ark\database\exception
 * @since 1.8.0
 *
 * When PDO `exec` or PDO Statement `execute` returns `false`
 */
class ArkPDOExecuteFailedError extends Exception
{
    /**
     * @var string
     */
    protected $relatedSQL;
    /**
     * @var string
     */
    protected $pdoErrorMessage;

    /**
     * @return string
     */
    public function getPdoErrorMessage(): string
    {
        return $this->pdoErrorMessage;
    }

    /**
     * @return string
     */
    public function getRelatedSQL()
    {
        return $this->relatedSQL;
    }

    public function __construct($relatedSQL = '', $pdo_error = '', $code = 0, Throwable $previous = null)
    {
        $message = 'SQL queried but error occurred.';
        $this->relatedSQL = $relatedSQL;
        if (!empty($relatedSQL)) {
            $relatedSQL = " SQL: " . $relatedSQL;
        }
        $this->pdoErrorMessage = $pdo_error;
        if (!empty($pdo_error)) {
            $pdo_error = 'PDO Error: ' . $pdo_error;
        }

        parent::__construct(
            $message . $relatedSQL . $pdo_error,
            $code,
            $previous
        );

        // bug fix in 1.8.12
    }
}