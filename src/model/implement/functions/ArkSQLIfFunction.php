<?php

namespace sinri\ark\database\model\implement\functions;

use sinri\ark\database\model\ArkSQLFunction;
use sinri\ark\database\pdo\ArkPDO;

/**
 * If expr1 is TRUE (expr1 <> 0 and expr1 <> NULL), IF() returns expr2. Otherwise, it returns expr3.
 */
class ArkSQLIfFunction extends ArkSQLFunction
{
    protected $target; // expr1
    protected $resultForTrue;// expr2
    protected $resultForFalse;// expr3

    public static function check($target, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        $x = new static('IF');
        $x->target = ArkPDO::quoteScalar($target, $quoteType);
        $x->resultForTrue = ArkPDO::CONST_NULL;
        $x->resultForFalse = ArkPDO::CONST_NULL;
        return $x;
    }

    public function setResultForTrue($result, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        $this->resultForTrue = ArkPDO::quoteScalar($result, $quoteType);
        return $this;
    }

    public function setResultForFalse($result, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        $this->resultForFalse = ArkPDO::quoteScalar($result, $quoteType);
        return $this;
    }

    public function makeFunctionSQL(): string
    {
        $this->functionParameterArray = [
            $this->target,
            $this->resultForTrue,
            $this->resultForFalse,
        ];
        return parent::makeFunctionSQL();
    }

}