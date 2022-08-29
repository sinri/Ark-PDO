<?php

use sinri\ark\core\ArkLogger;
use sinri\ark\database\model\ArkDatabaseDynamicTableModel;
use sinri\ark\database\model\ArkSQLCondition;
use sinri\ark\database\pdo\ArkPDO;
use sinri\ark\database\pdo\engine\ArkPDOConfigForSqlite;

require_once __DIR__ . '/../../vendor/autoload.php';
$config = new ArkPDOConfigForSqlite();
$sqlite_db_file = __DIR__ . '/../../log/sqlite-test.db';
$config->setAddress($sqlite_db_file);
$pdo = new ArkPDO($config);
$pdo->connect();

$logger = new ArkLogger(__DIR__ . '/../../log', 'sqlite');

$created = $pdo->safeExecute("CREATE TABLE COMPANY(
   ID INTEGER PRIMARY KEY   AUTOINCREMENT,
   NAME           TEXT      NOT NULL,
   AGE            INT       NOT NULL,
   ADDRESS        CHAR(50),
   SALARY         REAL
);");
$logger->info('created table', [$created]);

$table = new ArkDatabaseDynamicTableModel($pdo, 'COMPANY');
$r = $table->insertOneRow(['NAME' => 'N1', 'AGE' => 45, 'ADDRESS' => 'TOKYO', 'SALARY' => 534.50]);
$logger->info('LID: ' . $r->getLastInsertedID());

$r = $table->insertOneRow(['NAME' => 'N2', 'AGE' => 35, 'ADDRESS' => 'SAPPORO', 'SALARY' => 123.50]);
$logger->info('LID: ' . $r->getLastInsertedID());

$result = $table->selectInTable()
    ->addCondition(ArkSQLCondition::for("AGE")->greaterThan(40))
    ->queryForRows();
foreach ($result->getResultRows() as $row) {
    $logger->info("SELECTED ROW: " . $row->getField('NAME'));
}

unlink($sqlite_db_file);
