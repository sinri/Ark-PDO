<?php


namespace sinri\ark\database\exception;


use RangeException;
use Throwable;

/**
 * Class ArkPDOInvalidIndexError
 * @package sinri\ark\database\Exception
 * @since 2.0.14
 * @since 2.0.23 becomes subclass of RangeException
 * @since 2.0.25 changed construction and exclude the situation of empty result
 */
class ArkPDOInvalidIndexError extends RangeException
{
    /**
     * @var int
     */
    protected $givenIndex;

    /**
     * ArkPDOInvalidIndexError constructor.
     * @param string $message
     * @param int $givenIndex
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message, int $givenIndex, $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->givenIndex = $givenIndex;
    }

    /**
     * @return int
     */
    public function getGivenIndex()
    {
        return $this->givenIndex;
    }

    /**
     * @param int $givenIndex
     * @return ArkPDOInvalidIndexError
     */
    public function setGivenIndex($givenIndex): ArkPDOInvalidIndexError
    {
        $this->givenIndex = $givenIndex;
        return $this;
    }
}