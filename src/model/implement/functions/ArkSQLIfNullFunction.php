<?php

namespace sinri\ark\database\model\implement\functions;

use sinri\ark\database\model\ArkSQLFunction;
use sinri\ark\database\pdo\ArkPDO;

/**
 * If expr1 is not NULL, IFNULL() returns expr1; otherwise it returns expr2.
 * @since 2.1 reconstructed
 */
class ArkSQLIfNullFunction extends ArkSQLFunction
{
    protected $target;
    protected $resultForNull = ArkPDO::CONST_NULL;

    public static function check($target, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        $x = new static('IFNULL');
        $x->target = ArkPDO::quoteScalar($target, $quoteType);
        return $x;
    }

    public function setResultForNull($result, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        $this->resultForNull = ArkPDO::quoteScalar($result, $quoteType);
        return $this;
    }

    public function makeFunctionSQL(): string
    {
        $this->functionParameterArray = [
            $this->target,
            $this->resultForNull,
        ];
        return parent::makeFunctionSQL();
    }
}