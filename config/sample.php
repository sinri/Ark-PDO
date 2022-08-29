<?php
//require_once __DIR__ . '/../../../vendor/autoload.php';
//require_once __DIR__ . '/../../../autoload.php';


use sinri\ark\database\pdo\engine\ArkPDOConfigForMySQL;

$pdoInfo = [
    "host" => "",
    "port" => "",
    "username" => "",
    "password" => "",
    "database" => "",
    "charset" => ArkPDOConfigForMySQL::CHARSET_UTF8,
    "engine" => ArkPDOConfigForMySQL::ENGINE,
];

$config = new ArkPDOConfigForMySQL();

$config->setHost($pdoInfo['host'])
    ->setPort($pdoInfo['port'])
    ->setUsername($pdoInfo['username'])
    ->setPassword($pdoInfo['password'])
    ->setDatabase($pdoInfo['database'])
    ->setCharset($pdoInfo['charset'])
    ->setEngine($pdoInfo['engine']);
