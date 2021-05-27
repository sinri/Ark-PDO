<?php

class X
{
    /**
     *
     */
    function Y1()
    {
        throw new RuntimeException('y1');
    }

    /**
     *
     */
    function Y2()
    {
        throw new LogicException('y2');
    }

    /**
     * @throws Exception
     */
    function Y3()
    {
        throw new Exception('y3');
    }
}