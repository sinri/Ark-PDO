<?php

namespace sinri\ark\database\test\mysql\grammar\version57\literal;

use InvalidArgumentException;

/**
 * @see https://dev.mysql.com/doc/refman/5.7/en/number-literals.html
 */
class NumericLiteral extends Literal
{
    /**
     * @var int|null
     */
    protected $exponent;

    /**
     * @param numeric $value
     * @param int|null $exponent
     */
    public function __construct($value, int $exponent = null)
    {
        if (!is_numeric($value)) {
            throw new InvalidArgumentException("numeric!");
        }
        if (preg_match("/^([+-]?\d+)(\.\d*)?$/", "$value", $matches)) {
            $intPart = $matches[1];
            $this->value = "$intPart";
            if (count($matches) > 1) {
                $mantissaPart = $matches[2];
                if (strlen($mantissaPart) > 1) {
                    $this->value = $matches[0];
                }
            }
        } else {
            throw new InvalidArgumentException("numeric!!");
        }

        $this->exponent = $exponent;
    }

    public function output(): string
    {
        $s = $this->value;
        if ($this->exponent !== null) {
            $s .= "E" . $this->exponent;
        }
        return $s;
    }
}