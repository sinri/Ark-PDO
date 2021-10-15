<?php

namespace sinri\ark\database\model\implement\functions;

use sinri\ark\database\model\ArkSQLFunction;
use sinri\ark\database\pdo\ArkPDO;

/**
 * Returns NULL if expr1 = expr2 is true, otherwise returns expr1.
 * This is the same as CASE WHEN expr1 = expr2 THEN NULL ELSE expr1 END.
 *
 * Note: MySQL evaluates expr1 twice if the arguments are not equal.
 *
 * @since 2.1 reconstructed
 */
class ArkSQLNullIfFunction extends ArkSQLFunction
{
    protected $target;
    protected $standardForNull = ArkPDO::CONST_NULL;

    public static function check($target, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        $x = new static('NULLIF');
        $x->target = ArkPDO::quoteScalar($target, $quoteType);
        return $x;
    }

    public function setStandardForNull($standard, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        $this->standardForNull = ArkPDO::quoteScalar($standard, $quoteType);
        return $this;
    }

    public function makeFunctionSQL(): string
    {
        $this->functionParameterArray = [
            $this->target,
            $this->standardForNull,
        ];
        return parent::makeFunctionSQL();
    }
}