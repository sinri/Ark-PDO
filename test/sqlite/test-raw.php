<?php

use sinri\ark\core\ArkLogger;
use sinri\ark\database\pdo\ArkPDO;
use sinri\ark\database\pdo\engine\ArkPDOConfigForSqlite;

require_once __DIR__ . '/../../vendor/autoload.php';
$config = new ArkPDOConfigForSqlite();
$sqlite_db_file = __DIR__ . '/../log/sqlite-test.db';
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

$id = $pdo->safeInsertOne("INSERT INTO COMPANY (NAME,AGE,ADDRESS,SALARY)
VALUES ( 'Paul', 32, 'California', 20000.00 );");
$logger->info('inserted one', [$id]);
$id = $pdo->safeInsertOne("INSERT INTO COMPANY (NAME,AGE,ADDRESS,SALARY)
VALUES ('Allen', 25, 'Texas', 15000.00 );");
$logger->info('inserted another', [$id]);

$all = $pdo->safeQueryAll("SELECT id,name FROM company");
$logger->info('select all', [$all]);

$dropped = $pdo->safeExecute("DROP TABLE COMPANY");
$logger->info('dropped table', [$dropped]);

unlink($sqlite_db_file);