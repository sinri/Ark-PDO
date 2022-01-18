<?php

namespace sinri\ark\database\test\mysql\grammar\version57\object;

use sinri\ark\database\test\mysql\grammar\SQLComponentInterface;

class Identifier implements SQLComponentInterface
{
    /**
     * @var string[]
     */
    protected $names;

    public function __construct(string $name, bool $escaped = true)
    {
        if ($escaped) {
            $this->names = ['`' . $name . '`'];
        } else {
            $this->names = [$name];
        }
    }

    public function append(string $name, bool $escaped = true)
    {
        if ($escaped) {
            $this->names[] = '`' . $name . '`';
        } else {
            $this->names[] = $name;
        }
        return $this;
    }

    public function output(): string
    {
//        $x=[];
//        foreach ($this->names as $name){
//            $x[]='`'.$name.'`';
//        }
//        return implode(".",$x);

        return implode('.', $this->names);
    }
}