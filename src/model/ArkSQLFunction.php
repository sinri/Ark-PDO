<?php


namespace sinri\ark\database\model;

use sinri\ark\database\pdo\ArkPDO;

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
     * @var string
     */
    protected $headText = '';
    /**
     * @var string
     */
    protected $tailText = '';
    /**
     * @var scalar[]
     */
    protected $functionParameterArray;

    /**
     * ArkSQLFunction constructor.
     * @param string $functionName
     * @param array $functionParameterArray Each item are raw
     */
    public function __construct(string $functionName, array $functionParameterArray = [])
    {
        $this->functionName = $functionName;
        $this->functionParameterArray = $functionParameterArray;
    }

    /**
     * @return string
     */
    public function getHeadText(): string
    {
        return $this->headText;
    }

    /**
     * @param string $headText
     * @return ArkSQLFunction
     */
    public function setHeadText(string $headText): ArkSQLFunction
    {
        $this->headText = $headText;
        return $this;
    }

    /**
     * @return string
     */
    public function getTailText(): string
    {
        return $this->tailText;
    }

    /**
     * @param string $tailText
     * @return ArkSQLFunction
     */
    public function setTailText(string $tailText): ArkSQLFunction
    {
        $this->tailText = $tailText;
        return $this;
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
    public function appendParameter($x, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        $this->functionParameterArray[] = ArkPDO::quoteScalar($x, $quoteType);
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
     * @since 2.0.25
     */
    public function __toString()
    {
        return $this->makeFunctionSQL();
    }

    /**
     * @return string
     */
    public function makeFunctionSQL(): string
    {
        return $this->functionName . "("
            . (empty($this->headText) ? '' : ' ')
            . $this->headText
            . implode(" , ", $this->functionParameterArray)
            . (empty($this->tailText) ? '' : ' ')
            . $this->tailText
            . ")";
    }
}