<?php

ini_set('display_errors', true);
error_reporting(E_ALL);

$autoloader = require __DIR__ . '/../vendor/autoload.php';

$config = array(
    'driver'   => 'mysql',
    'hostname' => 'localhost',
    'database' => 'koine_repository',
    'username' => 'root',
    'password' => '',
);

if (file_exists(__DIR__ . '/db_config.php')) {
    $config = require __DIR__ . '/db_config.php';
}

$driver   = $config['driver'];
$hostname = $config['hostname'];
$database = $config['database'];
$password = $config['password'];
$username = $config['username'];

$db = getenv('DB');

if ($db) {
    $drivers = array(
        'mysql'  => 'mysql',
        'sqlite' => 'sqlite',
        'pg'     => 'pgsql',
    );

    $driver = $drivers[$db];
}

$connection = new PDO("$driver:host=$hostname;", $username, $password);
$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$connection->exec('SET CHARACTER SET utf8');

$testTable = <<<SQL
    CREATE TABLE `test_table` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
      `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL;

try {
    $connection->query("CREATE DATABASE IF NOT EXISTS $database");
    $connection->query('USE ' . $database);
    $connection->query($testTable);
} catch (PDOException $e) {
}

\Koine\Repository\Test\DbTestCase::setConnection($connection);

require_once 'helpers/TestTableEntity.php';
