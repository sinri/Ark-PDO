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
    protected $functionName;
    /**
     * @var scalar[]
     */
    protected $functionParameterArray;

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
    public function resetParameterArray()
    {
        $this->functionParameterArray = [];
        return $this;
    }

    /**
     * @return array
     */
    public function getParameterArray()
    {
        return $this->functionParameterArray;
    }

    /**
     * @param scalar $x
     * @return $this
     */
    public function appendParameter($x)
    {
        $this->functionParameterArray[] = $x;
        return $this;
    }

    /**
     * @return string
     */
    public function getFunctionName()
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

}