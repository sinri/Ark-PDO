<?php


namespace sinri\ark\database\Exception;


use Exception;
use Throwable;

/**
 * Class ArkPDOInvalidIndexError
 * @package sinri\ark\database\Exception
 * @since 2.0.14
 */
class ArkPDOInvalidIndexError extends Exception
{
    protected $expectedIndex;

    /**
     * ArkPDOInvalidIndexError constructor.
     * @param string $message
     * @param string|int $expectedIndex
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message, $expectedIndex, $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->expectedIndex = $expectedIndex;
    }

    /**
     * @return string|int
     */
    public function getExpectedIndex()
    {
        return $this->expectedIndex;
    }

    /**
     * @param string|int $expectedIndex
     * @return ArkPDOInvalidIndexError
     */
    public function setExpectedIndex($expectedIndex): ArkPDOInvalidIndexError
    {
        $this->expectedIndex = $expectedIndex;
        return $this;
    }
}