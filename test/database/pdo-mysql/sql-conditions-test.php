<?php

use sinri\ark\database\model\ArkSQLCondition;
use sinri\ark\database\model\implement\ArkSQLAggregateFunction;

require_once __DIR__ . '/../../../vendor/autoload.php';

$x1 = ArkSQLCondition::and(
    [
        ArkSQLCondition::for("a")->equal(1),
        ArkSQLCondition::for("b", ArkSQLCondition::QUOTE_TYPE_FIELD)->notEqual(2),
        ArkSQLCondition::or(
            [
                ArkSQLCondition::for("c")->between(2, 4),
                ArkSQLCondition::for(1, ArkSQLCondition::QUOTE_TYPE_INT)->in([34, 66]),
            ]
        ),
        ArkSQLCondition::for(
            ArkSQLAggregateFunction::makeCount('x')
        )->greaterThan(2),
        ArkSQLCondition::for(1)->greaterThan(0),
    ]
);
echo $x1->makeConditionSQL() . PHP_EOL;