<?php

namespace sinri\ark\database\model;

use sinri\ark\database\exception\ArkPDOSQLBuilderError;
use sinri\ark\database\pdo\ArkPDO;

/**
 * Greatly Upgraded since 2.1.x
 */
class ArkSQLCondition
{
    const OP_EQ = "=";
    const OP_GT = ">";
    const OP_EGT = ">=";
    const OP_LT = "<";
    const OP_ELT = "<=";
    const OP_NEQ = "<>";
    const OP_NULL_SAFE_EQUAL = "<=>";
    const OP_IS = "IS";
    const OP_IS_NOT = "IS NOT";
    const OP_IN = "IN";
    const OP_NOT_IN = "NOT IN";
    const OP_LIKE = "LIKE";
    const OP_NOT_LIKE = "NOT LIKE";
    const OP_BETWEEN = "BETWEEN";
    const OP_NOT_BETWEEN = "NOT BETWEEN";

    const MACRO_IS_NULL_OR_EMPTY_STRING = "MACRO_IS_NULL_OR_EMPTY_STRING";
    const MACRO_IS_NOT_NULL_NOR_EMPTY_STRING = "MACRO_IS_NOT_NULL_NOR_EMPTY_STRING";

    const MACRO_RAW_EXPRESSION = "MACRO_RAW_EXPRESSION";

    const OP_EXISTS = "EXISTS";
    const OP_NOT_EXISTS = "NOT EXISTS";

    const OP_PARENTHESES_AND = "AND";
    const OP_PARENTHESES_OR = "OR";

    const CONST_TRUE = "TRUE";
    const CONST_FALSE = "FALSE";
    const CONST_NULL = "NULL";

    const QUOTE_TYPE_RAW = 'RAW';
    const QUOTE_TYPE_FIELD = 'FIELD';
    const QUOTE_TYPE_VALUE = 'VALUE';
    const QUOTE_TYPE_INT = 'INT';
    const QUOTE_TYPE_FLOAT = 'FLOAT';
    const QUOTE_TYPE_STRING = 'STRING';

    protected $leftSide;
    protected $operator;
    protected $rightSide;

    protected function __construct($leftSide = null, $operator = null, $rightSide = null)
    {
        $this->leftSide = $leftSide;
        $this->operator = $operator;
        $this->rightSide = $rightSide;
    }

    /**
     * @param ArkSQLCondition[] $conditions
     * @return string
     * @throws ArkPDOSQLBuilderError
     * @since 2.0
     */
    public static function generateConditionSQLComponent(array $conditions): string
    {
        $condition_sql = [];
        foreach ($conditions as $condition) {
            $condition_sql[] = $condition->makeConditionSQL();
        }
        if (empty($condition_sql)) {
            $condition_sql = '1=1';
        } else {
            $condition_sql = implode(' AND ', $condition_sql);
        }
        return $condition_sql;
    }

    /**
     * @param string $leftSide
     * @param string $quoteType
     * @return ArkSQLCondition
     */
    public static function for(string $leftSide, string $quoteType = self::QUOTE_TYPE_RAW)
    {
        $x = new self();
        $x->leftSide = self::quoteScalar($leftSide, $quoteType);
        return $x;
    }

    /**
     * @param scalar $x
     * @param string $quoteType
     * @return string
     */
    protected static function quoteScalar($x, $quoteType): string
    {
        if ($quoteType === self::QUOTE_TYPE_RAW) {
            // $x must be a variable that could be transformed into string
            return $x;
        }
        if ($quoteType === self::QUOTE_TYPE_FIELD) {
            // $x must be a string
            return '`' . $x . '`';
        }
        if ($quoteType === self::QUOTE_TYPE_VALUE) {
            if ($x === null) {
                return self::CONST_NULL;
            }
            if ($x === false) {
                return self::CONST_FALSE;
            }
            if ($x === true) {
                return self::CONST_TRUE;
            }
            if (is_int($x)) {
                $quoteType = self::QUOTE_TYPE_INT;
            } elseif (is_float($x)) {
                $quoteType = self::QUOTE_TYPE_FLOAT;
            } else {
                $quoteType = self::QUOTE_TYPE_STRING;
            }
        }
        switch ($quoteType) {
            case self::QUOTE_TYPE_INT:
                return '' . intval($x);
            case self::QUOTE_TYPE_FLOAT:
                return '' . floatval($x);
            case self::QUOTE_TYPE_STRING:
            default:
                return ArkPDO::dryQuote($x);
        }
    }

    public static function raw(string $raw)
    {
        $x = new self();
        $x->operator = self::MACRO_RAW_EXPRESSION;
        $x->leftSide = $raw;
        $x->rightSide = '';
        return $x;
    }

    public static function exists(string $subQuery)
    {
        $x = new self();
        $x->operator = self::OP_EXISTS;
        $x->leftSide = '';
        $x->rightSide = $subQuery;
        return $x;
    }

    public static function notExists(string $subQuery)
    {
        $x = new self();
        $x->operator = self::OP_NOT_EXISTS;
        $x->leftSide = '';
        $x->rightSide = $subQuery;
        return $x;
    }

    /**
     * @param ArkSQLCondition[] $conditions
     * @return ArkSQLCondition
     */
    public static function and(array $conditions)
    {
        $x = new self();
        $x->operator = self::OP_PARENTHESES_AND;
        $x->leftSide = '';
        $x->rightSide = [];
        foreach ($conditions as $condition) {
            $x->rightSide[] = $condition;
        }
        return $x;
    }

    /**
     * @param ArkSQLCondition[] $conditions
     * @return ArkSQLCondition
     */
    public static function or(array $conditions)
    {
        $x = new self();
        $x->operator = self::OP_PARENTHESES_OR;
        $x->leftSide = '';
        $x->rightSide = [];
        foreach ($conditions as $condition) {
            $x->rightSide[] = $condition;
        }
        return $x;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->makeConditionSQL();
    }

    public function makeConditionSQL(): string
    {
        switch ($this->operator) {
            case self::OP_EQ:
            case self::OP_GT:
            case self::OP_EGT:
            case self::OP_LT:
            case self::OP_ELT:
            case self::OP_NEQ:
            case self::OP_NULL_SAFE_EQUAL:
            case self::OP_IS:
            case self::OP_IS_NOT:
                return $this->leftSide . " " . $this->operator . " " . $this->rightSide;
            case self::OP_IN:
            case self::OP_NOT_IN:
                if (!is_array($this->rightSide) || empty($this->rightSide)) {
                    throw new ArkPDOSQLBuilderError(
                        "ERROR, YOU MUST GIVE AN ARRAY WITH ITEM(s) FOR IN OPERATION!",
                        "{$this->leftSide} {$this->operator} " . json_encode($this->rightSide)
                    );
                }
                return $this->leftSide . " " . $this->operator . " (" . implode(",", $this->rightSide) . ")";
            case self::OP_LIKE:
            case self::OP_NOT_LIKE:
                // NOTE: value is preprocessed in constructor
                return $this->leftSide . " " . $this->operator . " " . $this->rightSide;
            case self::OP_BETWEEN:
            case self::OP_NOT_BETWEEN:
                return $this->leftSide . " " . $this->operator . " " . $this->rightSide[0] . " AND " . $this->rightSide[1];
            case self::OP_EXISTS:
            case self::OP_NOT_EXISTS:
                // NOTE: only value is used as raw sql string @since 1.5
                return $this->operator . "(" . $this->rightSide . ")";
            case self::OP_PARENTHESES_AND:
            case self::OP_PARENTHESES_OR:
                // NOTE: to support the parentheses @since 1.7.1
                $parts = [];
                if (is_array($this->rightSide)) {
                    foreach ($this->rightSide as $subSQLCondition) {
                        $parts[] = $subSQLCondition->makeConditionSQL();
                    }
                }
                if (empty($parts)) {
                    throw new ArkPDOSQLBuilderError(
                        "Condition Set Empty",
                        "{$this->operator} ()"
                    );
                }
                return '(' . implode(" " . $this->operator . " ", $parts) . ')';
            case self::MACRO_IS_NULL_OR_EMPTY_STRING:
                return "(`{$this->leftSide}` IS NULL OR `{$this->leftSide}` = '')";
            case self::MACRO_IS_NOT_NULL_NOR_EMPTY_STRING:
                return "(`{$this->leftSide}` IS NOT NULL AND `{$this->leftSide}` <> '')";
            case self::MACRO_RAW_EXPRESSION:
                return $this->leftSide;
            default:
                throw new ArkPDOSQLBuilderError("ERROR, UNKNOWN OPERATE", json_encode($this->operator));
        }
    }

    /**
     * @param scalar $rightSide
     * @param string $quoteType
     * @return $this
     */
    public function notEqualNullSafe($rightSide, $quoteType = self::QUOTE_TYPE_VALUE)
    {
        $this->operator = self::OP_NULL_SAFE_EQUAL;
        $this->rightSide = self::quoteScalar($rightSide, $quoteType);
        return $this;
    }

    public function equalOrIn($x, $quoteType = self::QUOTE_TYPE_VALUE)
    {
        if (is_array($x)) {
            return $this->in($x, $quoteType);
        }
        return $this->equal($x, $quoteType);
    }

    /**
     * @param scalar[] $array
     * @param string $quoteType
     */
    public function in(array $array, $quoteType = self::QUOTE_TYPE_VALUE)
    {
        $this->operator = self::OP_IN;
        $x = [];
        foreach ($array as $item) {
            if (!is_scalar($item)) {
                throw new ArkPDOSQLBuilderError("ARRAY ITEM NOT SCALAR", json_encode($item));
            }
            $x[] = self::quoteScalar($item, $quoteType);
        }
        $this->rightSide = $x;
        return $this;
    }

    /**
     * @param scalar $rightSide
     * @param string $quoteType
     * @return $this
     */
    public function equal($rightSide, $quoteType = self::QUOTE_TYPE_VALUE)
    {
        $this->operator = self::OP_EQ;
        $this->rightSide = self::quoteScalar($rightSide, $quoteType);
        return $this;
    }

    public function notEqualNorIn($x, $quoteType = self::QUOTE_TYPE_VALUE)
    {
        if (is_array($x)) {
            return $this->notIn($x, $quoteType);
        }
        return $this->notEqual($x, $quoteType);
    }

    /**
     * @param scalar[] $array
     * @param string $quoteType
     */
    public function notIn(array $array, $quoteType = self::QUOTE_TYPE_VALUE)
    {
        $this->operator = self::OP_NOT_IN;
        $x = [];
        foreach ($array as $item) {
            if (!is_scalar($item)) {
                throw new ArkPDOSQLBuilderError("ARRAY ITEM NOT SCALAR", json_encode($item));
            }
            $x[] = self::quoteScalar($item, $quoteType);
        }
        $this->rightSide = $x;
        return $this;
    }

    /**
     * @param scalar $rightSide
     * @param string $quoteType
     * @return $this
     */
    public function notEqual($rightSide, $quoteType = self::QUOTE_TYPE_VALUE)
    {
        $this->operator = self::OP_NEQ;
        $this->rightSide = self::quoteScalar($rightSide, $quoteType);
        return $this;
    }

    public function greaterThan($x, $quoteType = self::QUOTE_TYPE_VALUE)
    {
        $this->operator = self::OP_GT;
        $this->rightSide = self::quoteScalar($x, $quoteType);
        return $this;
    }

    public function greaterThanOrEqual($x, $quoteType = self::QUOTE_TYPE_VALUE)
    {
        $this->operator = self::OP_EGT;
        $this->rightSide = self::quoteScalar($x, $quoteType);
        return $this;
    }

    public function lessThan($x, $quoteType = self::QUOTE_TYPE_VALUE)
    {
        $this->operator = self::OP_LT;
        $this->rightSide = self::quoteScalar($x, $quoteType);
        return $this;
    }

    public function lessThanOrEqual($x, $quoteType = self::QUOTE_TYPE_VALUE)
    {
        $this->operator = self::OP_ELT;
        $this->rightSide = self::quoteScalar($x, $quoteType);
        return $this;
    }

    public function isNull()
    {
        $this->operator = self::OP_IS;
        $this->rightSide = self::CONST_NULL;
        return $this;
    }

    public function isNotNull()
    {
        $this->operator = self::OP_IS_NOT;
        $this->rightSide = self::CONST_NULL;
        return $this;
    }

    public function havePrefix(string $prefix)
    {
        return $this->like($prefix . '%');
    }

    /**
     * @param string $x such as `%A%B%` and the entire string would be quoted
     */
    public function like(string $x)
    {
        $this->operator = self::OP_LIKE;
        $this->rightSide = ArkPDO::dryQuote($x);
        return $this;
    }

    public function notHavePrefix(string $prefix)
    {
        return $this->notLike($prefix . '%');
    }

    /**
     * @param string $x such as `%A%B%` and the entire string would be quoted
     */
    public function notLike(string $x)
    {
        $this->operator = self::OP_NOT_LIKE;
        $this->rightSide = ArkPDO::dryQuote($x);
        return $this;
    }

    public function haveSuffix(string $suffix)
    {
        return $this->like('%' . $suffix);
    }

    public function notHaveSuffix(string $suffix)
    {
        return $this->notLike('%' . $suffix);
    }

    public function contain(string $substring)
    {
        return $this->like('%' . $substring . '%');
    }

    public function notContain(string $substring)
    {
        return $this->notLike('%' . $substring . '%');
    }

    /**
     * @param scalar $a
     * @param scalar $b
     * @param string $quoteType
     * @return $this
     */
    public function between($a, $b, $quoteType = self::QUOTE_TYPE_VALUE)
    {
        $this->operator = self::OP_BETWEEN;
        $this->rightSide = [
            self::quoteScalar($a, $quoteType),
            self::quoteScalar($b, $quoteType),
        ];
        return $this;
    }

    /**
     * @param scalar $a
     * @param scalar $b
     * @param string $quoteType
     * @return $this
     */
    public function notBetween($a, $b, $quoteType = self::QUOTE_TYPE_VALUE)
    {
        $this->operator = self::OP_NOT_BETWEEN;
        $this->rightSide = [
            self::quoteScalar($a, $quoteType),
            self::quoteScalar($b, $quoteType),
        ];
        return $this;
    }

    public function isNullOrEmptyString()
    {
        $this->operator = self::MACRO_IS_NULL_OR_EMPTY_STRING;
        $this->rightSide = null;
        return $this;
    }

    public function isNotNullNorEmptyString()
    {
        $this->operator = self::MACRO_IS_NOT_NULL_NOR_EMPTY_STRING;
        $this->rightSide = null;
        return $this;
    }
}