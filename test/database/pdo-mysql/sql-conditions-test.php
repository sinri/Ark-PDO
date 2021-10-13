<?php

use sinri\ark\database\model\ArkSQLCondition;
use sinri\ark\database\model\implement\functions\ArkSQLAggregateFunction;
use sinri\ark\database\model\implement\functions\ArkSQLCaseFunction;
use sinri\ark\database\model\implement\functions\ArkSQLCastFunction;
use sinri\ark\database\model\implement\functions\ArkSQLIfFunction;
use sinri\ark\database\model\implement\functions\ArkSQLMathematicalFunction;
use sinri\ark\database\pdo\ArkPDO;

require_once __DIR__ . '/../../../vendor/autoload.php';

$x1 = ArkSQLCondition::and(
    [
        ArkSQLCondition::for("a")->equal(1),
        ArkSQLCondition::for("b", ArkPDO::QUOTE_TYPE_FIELD)->notEqual(2),
        ArkSQLCondition::or(
            [
                ArkSQLCondition::for("c")->between(2, 4),
                ArkSQLCondition::for(1, ArkPDO::QUOTE_TYPE_INT)->in([34, 66]),
            ]
        ),
        ArkSQLCondition::for(
            ArkSQLAggregateFunction::makeCount('x')
        )->greaterThan(2),
        ArkSQLCondition::for(1)->greaterThan(0),
        ArkSQLCondition::for(
            ArkSQLCaseFunction::makeCaseFunction('x')
                ->when('A')->then('B()', ArkPDO::QUOTE_TYPE_RAW)
                ->when('C')->then('D')
                ->else('0', ArkPDO::QUOTE_TYPE_VALUE)
        )->equal('D'),
        ArkSQLCondition::for(
            ArkSQLIfFunction::check('P', ArkPDO::QUOTE_TYPE_FIELD)
                ->setResultForTrue(ArkSQLMathematicalFunction::makeAbs('P', ArkPDO::QUOTE_TYPE_FIELD))
                ->setResultForFalse(ArkSQLCastFunction::makeConvertEncoding('Q', 'GBK'))
        )->lessThanOrEqual(
            ArkSQLMathematicalFunction::makeRand()
        ),
    ]
);
echo $x1->makeConditionSQL() . PHP_EOL;