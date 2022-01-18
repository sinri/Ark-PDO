<?php

namespace sinri\ark\database\test\mysql\grammar\version57\expr;

use sinri\ark\database\test\mysql\grammar\SQLComponentInterface;

class BooleanPrimary implements SQLComponentInterface
{
    /**
     * @var string
     */
    protected $raw;

    public function __construct(Predicate $predicate)
    {
        $this->raw = $predicate->output();
    }

    public function isNull()
    {
        $this->raw .= ' IS NULL';
        return $this;
    }

    public function isNotNull()
    {
        $this->raw .= ' IS NOT NULL';
        return $this;
    }

    public function nullSafeEqual(Predicate $predicate)
    {
        $this->raw .= ' <=> ' . $predicate->output();
        return $this;
    }

    public function compareWithPredicate(ComparisonOperator $comparisonOperator, Predicate $predicate)
    {
        $this->raw .= ' ' . $comparisonOperator->output() . ' ' . $predicate->output();
        return $this;
    }

    public function output(): string
    {
        return $this->raw;
    }
}