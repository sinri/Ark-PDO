<?php

namespace sinri\ark\database\model\implement\functions;

use sinri\ark\database\exception\ArkPDOSQLBuilderError;
use sinri\ark\database\model\ArkSQLFunction;
use sinri\ark\database\pdo\ArkPDO;

class ArkSQLCaseFunction extends ArkSQLFunction
{
    /**
     * @var bool
     */
    protected $asStatement;
    /**
     * @var string
     */
    protected $tempWhen;

    public function __construct(string $functionName, array $functionParameterArray = [], $asStatement = false)
    {
        parent::__construct($functionName, $functionParameterArray);
        $this->asStatement = $asStatement;
    }

    public static function makeCaseFunction($target, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        return new self(
            'CASE',
            [
                'target' => ArkPDO::quoteScalar($target, $quoteType),
                'branches' => [],
                'else' => null,
            ]
        );
    }

    public static function makeCaseStatement($target, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        return new self(
            'CASE',
            [
                'target' => $target,
                'branches' => [],
                'else' => null,
            ],
            true
        );
    }

    public function when($x, $quoteType = ArkPDO::QUOTE_TYPE_VALUE)
    {
        $this->tempWhen = ArkPDO::quoteScalar($x, $quoteType);
        return $this;
    }

    public function then($x, $quoteType = ArkPDO::QUOTE_TYPE_VALUE)
    {
        if ($this->tempWhen === null) {
            throw new ArkPDOSQLBuilderError('WHEN NULL');
        }
        $this->functionParameterArray['branches'][$this->tempWhen] = ArkPDO::quoteScalar($x, $quoteType);
        return $this;
    }

    public function else($x, $quoteType = ArkPDO::QUOTE_TYPE_VALUE)
    {
        $this->functionParameterArray['else'] = ArkPDO::quoteScalar($x, $quoteType);
        return $this;
    }

    public function makeFunctionSQL(): string
    {
        $s = $this->functionName;
        $target = $this->readTarget();
        if ($target !== null) {
            $s .= " " . $target;
        }
        $branches = $this->readBranches();
        foreach ($branches as $when => $then) {
            $s .= ' WHEN ' . $when . ' THEN ' . $then;
        }
        $else = $this->readElse();
        if ($else !== null) {
            $s .= ' ELSE ' . $else;
        }
        $s .= ' END';
        if ($this->asStatement) {
            $s .= ' CASE';
        }

        return $s;
    }

    /**
     * @return string|null
     */
    public function readTarget()
    {
        $target = null;
        if (isset($this->functionParameterArray['target'])) {
            $target = $this->functionParameterArray['target'];
        }
        if ($target === null) return null;
        return $target;
    }

    /**
     * @return string[]
     */
    public function readBranches()
    {
        $branches = null;
        if (isset($this->functionParameterArray['branches'])) {
            $branches = $this->functionParameterArray['branches'];
        }
        if (!is_array($branches)) {
            throw new ArkPDOSQLBuilderError("CASE Branches is not an array!");
        }

        return $branches;
    }

    /**
     * @return string|null
     */
    public function readElse()
    {
        $else = null;
        if (isset($this->functionParameterArray['else'])) {
            $else = $this->functionParameterArray['else'];
        }
        if ($else === null) return null;
        return $else;
    }
}