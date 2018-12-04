<?php
//require_once __DIR__ . '/../../../vendor/autoload.php';
//require_once __DIR__ . '/../../../autoload.php';


$pdoInfo = [
    "host" => "",
    "port" => "",
    "username" => "",
    "password" => "",
    "database" => "",
    "charset" => \sinri\ark\database\pdo\ArkPDOConfig::CHARSET_UTF8,
    "engine" => \sinri\ark\database\pdo\ArkPDOConfig::ENGINE_MYSQL,
];

$config = new \sinri\ark\database\pdo\ArkPDOConfig();
/** @noinspection SpellCheckingInspection */
$config->setHost($pdoInfo['host'])
    ->setPort($pdoInfo['port'])
    ->setUsername($pdoInfo['username'])
    ->setPassword($pdoInfo['password'])
    ->setDatabase($pdoInfo['database'])
    ->setCharset($pdoInfo['charset'])
    ->setEngine($pdoInfo['engine']);
