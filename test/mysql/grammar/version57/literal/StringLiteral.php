<?php

namespace sinri\ark\database\test\mysql\grammar\version57\literal;

/**
 * @see https://dev.mysql.com/doc/refman/5.7/en/string-literals.html
 */
class StringLiteral extends Literal
{
    /**
     * @var string|null prefix
     */
    private $charset_name = null;
    /**
     * @var string|null suffix
     */
    private $collate = null;
    /**
     * @var bool
     */
    private $forLike;

    public function __construct(string $value, bool $forLike = false)
    {
        $this->value = $value;
        $this->forLike = $forLike;
    }

    /**
     * @param string|null $charset_name
     */
    public function setCharsetName(string $charset_name)
    {
        $this->charset_name = $charset_name;
        return $this;
    }

    /**
     * @param string|null $collate
     */
    public function setCollate(string $collate)
    {
        $this->collate = $collate;
        return $this;
    }

    public function output(): string
    {
        $s = self::quote($this->value, $this->forLike);
        if ($this->charset_name !== null) {
            $s = $this->charset_name . $s;
        }
        if ($this->collate !== null) {
            $s = $s . " COLLATE " . $this->collate;
        }
        return $s;
    }

    public static function quote(string $inputString, bool $forLike = false)
    {
        // Note: A backspace character is not supported here
        $a = ['\\', "\0", "\n", "\r", "'", '"', "\x1a", "\t"];
        $b = ['\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z', '\\t'];
        if ($forLike) {
            $a[] = "%";
            $b[] = "\%";
            $a[] = "_";
            $b[] = "\_";
        }
        $x = str_replace($a, $b, $inputString);
        return "'{$x}'";
    }
}