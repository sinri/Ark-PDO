<?php

namespace sinri\ark\database\test\mysql\grammar\version57\expr;

use sinri\ark\database\test\mysql\grammar\SQLComponentInterface;

class BitExpr implements SQLComponentInterface
{
    /**
     * @var string
     */
    protected $raw;

    public function __construct(SimpleExpr $simpleExpr)
    {
        $this->raw = $simpleExpr->output();
    }

    /**
     * @param String $operator & <<  >>  + -  *  /  DIV  MOD  %  ^
     * @param BitExpr $another
     * @return $this
     */
    public function operateWithAnother(string $operator, BitExpr $another)
    {
        $this->raw .= ' ' . $operator . ' ' . $another->output();
        return $this;
    }

    public function output(): string
    {
        return $this->raw;
    }

    public function plusInterval(TemporalInterval $interval)
    {
        $this->raw .= ' + ' . $interval->output();
        return $this;
    }

    public function minusInterval(TemporalInterval $interval)
    {
        $this->raw .= ' - ' . $interval->output();
        return $this;
    }
}