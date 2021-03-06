<?php


namespace sinri\ark\database\exception;


use Throwable;
use UnexpectedValueException;

/**
 * Class ArkPDOConfigError
 * @package sinri\ark\database\Exception
 * @since 2.0.13
 * @since 2.0.23 becomes subclass of UnexpectedValueException
 *
 * It raises when Ark PDO configuration is not in correct format.
 */
class ArkPDOConfigError extends UnexpectedValueException
{
    /**
     * @var string
     */
    protected $invalidFieldName;
    /**
     * @var int|string|null
     */
    protected $invalidFieldValue;

    public function __construct($invalidField = 'ALL', $invalidFieldValue = null, Throwable $previous = null)
    {
        parent::__construct(
            "Field [{$invalidField}] found invalid as [" . json_encode($invalidFieldValue) . "]",
            0,
            $previous
        );
        $this->invalidFieldName = $invalidField;
    }

    public function getInvalidFieldValue()
    {
        return $this->invalidFieldValue;
    }

    public function setInvalidFieldValue($invalidFieldValue): ArkPDOConfigError
    {
        $this->invalidFieldValue = $invalidFieldValue;
        return $this;
    }

    public function getInvalidFieldName(): string
    {
        return $this->invalidFieldName;
    }

    public function setInvalidFieldName(string $invalidFieldName): ArkPDOConfigError
    {
        $this->invalidFieldName = $invalidFieldName;
        return $this;
    }
}