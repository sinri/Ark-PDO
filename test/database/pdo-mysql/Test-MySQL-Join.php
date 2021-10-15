<?php

use sinri\ark\database\model\ArkDatabaseDynamicTableModel;
use sinri\ark\database\model\ArkSQLCondition;
use sinri\ark\database\model\query\ArkDatabaseSelectJoinedTablesQuery;
use sinri\ark\database\model\query\ArkDatabaseSelectTableQuery;
use sinri\ark\database\model\query\ArkDatabaseSelectUnionQuery;
use sinri\ark\database\pdo\ArkPDO;
use sinri\ark\database\pdo\ArkPDOConfig;

require_once __DIR__ . '/../../../vendor/autoload.php';

$config = new ArkPDOConfig();
require __DIR__ . '/config-MySQL-8.0.php';

$db = new ArkPDO();
$db->setPdoConfig($config);
$db->connect();

$tableC = new ArkDatabaseDynamicTableModel($db, 'c', 'sinri');
$tableD = new ArkDatabaseDynamicTableModel($db, 'd', 'sinri');

$selection = (new ArkDatabaseSelectTableQuery($tableC));
$selection->addSelectFieldByDetail($tableC->getFieldExpression('id'), 'ID');
$selection->addSelectFieldByDetail($tableD->getFieldExpression('score'), 'SCORE');
$selection->leftJoinAnotherTable(
    $tableD,
    [
        ArkSQLCondition::for($tableC->getFieldExpression('id'))
            ->equal($tableD->getFieldExpression('id'), ArkPDO::QUOTE_TYPE_RAW),
    ]
);
$selection->addCondition(
    ArkSQLCondition::for($tableD->getFieldExpression('value'))
        ->havePrefix('A')
);
$selection->setSortExpression('SCORE desc, ' . $tableC->getFieldExpression('name'));
echo $selection->generateSQL() . PHP_EOL;

$selection2 = new ArkDatabaseSelectTableQuery($tableD);
$selection2->addSelectFieldByDetail('id', 'ID');
$selection2->addSelectFieldByDetail('score', 'SCORE');
$selection2->addCondition(ArkSQLCondition::for('value')->notHaveSuffix('P'));

$unionSelection = new ArkDatabaseSelectUnionQuery($selection);
$unionSelection->union($selection2);
$unionSelection->setSortExpression('score desc');
echo $unionSelection->generateSQL() . PHP_EOL;

$selection3 = new ArkDatabaseSelectTableQuery($tableC);
$selection3->addSelectFieldByDetail($tableC->getFieldExpression('id'), 'ID');
$selection3->addSelectFieldByDetail($tableD->getFieldExpression('score'), 'SCORE');
$selection3->rightJoinAnotherTable(
    $tableD,
    [
        ArkSQLCondition::for($tableC->getFieldExpression('id'))
            ->equal($tableD->getFieldExpression('id'), ArkPDO::QUOTE_TYPE_RAW),
    ]
);
$selection3->addCondition(
    ArkSQLCondition::for($tableD->getFieldExpression('value'))
        ->havePrefix('A')
);
$selection3->setSortExpression('SCORE desc, ' . $tableC->getFieldExpression('name'));

$selection3->innerJoinQueryBlock(
    $unionSelection,
    't',
    [
        ArkSQLCondition::for('t.ID')->equal($tableC->getFieldExpression('id'))
    ]
);
echo $selection3->generateSQL() . PHP_EOL;