<?php

use sinri\ark\database\model\ArkSQLCondition;

require_once __DIR__ . '/../../../vendor/autoload.php';

$x1 = ArkSQLCondition::makeConditionsUnion([
    ArkSQLCondition::makeGreaterThan("a", 1),
    ArkSQLCondition::makeLessThan("b", 1),
    ArkSQLCondition::makeConditionsIntersect([
        ArkSQLCondition::makeBetween("c", 2, 4),
        ArkSQLCondition::makeInArray("d", [34, 66]),
    ])
]);
echo $x1->makeConditionSQL() . PHP_EOL;