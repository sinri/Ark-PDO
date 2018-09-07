<?php
//require_once __DIR__ . '/../../../vendor/autoload.php';
//require_once __DIR__ . '/../../../autoload.php';

$config = new \sinri\ark\database\pdo\ArkPDOConfig();
/** @noinspection SpellCheckingInspection */
$config->setHost('rdsjjw98q0455p35wi06o.mysql.rds.aliyuncs.com')
    ->setPort(3306)
    ->setUsername('office_test')
    ->setPassword('pz1ytq1WCT2Z2bNR')
    ->setDatabase('test')
    ->setCharset(\sinri\ark\database\pdo\ArkPDOConfig::CHARSET_UTF8)
    ->setEngine(\sinri\ark\database\pdo\ArkPDOConfig::ENGINE_MYSQL);
