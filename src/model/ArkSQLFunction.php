<?php


namespace sinri\ark\database\model;

/**
 * Class ArkSQLFunction
 * @package sinri\ark\database\model
 * @since 2.0.24 Experimental
 */
class ArkSQLFunction
{
    /**
     * @var string
     */
    protected string $functionName;
    /**
     * @var scalar[]
     */
    protected array $functionParameterArray;

    /**
     * ArkSQLFunction constructor.
     * @param string $functionName
     * @param array $functionParameterArray
     */
    public function __construct(string $functionName, array $functionParameterArray = [])
    {
        $this->functionName = $functionName;
        $this->functionParameterArray = $functionParameterArray;
    }

    /**
     * @return $this
     */
    public function resetParameterArray(): static
    {
        $this->functionParameterArray = [];
        return $this;
    }

    /**
     * @return array
     */
    public function getParameterArray(): array
    {
        return $this->functionParameterArray;
    }

    /**
     * @param scalar $x
     * @return $this
     */
    public function appendParameter($x): static
    {
        $this->functionParameterArray[] = $x;
        return $this;
    }

    /**
     * @return string
     */
    public function getFunctionName(): string
    {
        return $this->functionName;
    }

    /**
     * @return string
     */
    public function makeFunctionSQL(): string
    {
        return $this->functionName . "(" . implode(" , ", $this->functionParameterArray) . ")";
    }

    /**
     * @return string
     * @since 2.0.25
     */
    public function __toString()
    {
        return $this->makeFunctionSQL();
    }
}