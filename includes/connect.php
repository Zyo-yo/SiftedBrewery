<?php

$dsn = "mysql:host=localhost;dbname=sifted_brewery_cms;charset=utf8mb4";
$username = "root";
$password = "";

try {
    $db = new PDO($dsn, $username, $password);

    $db->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    $db->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );

    $db->setAttribute(
        PDO::ATTR_EMULATE_PREPARES,
        false
    );
} catch (PDOException $exception) {
    exit("Database connection failed.");
}