<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$is_authenticated =
    isset($_SESSION["authenticated"]) &&
    $_SESSION["authenticated"] === true &&
    isset($_SESSION["user_id"]) &&
    isset($_SESSION["username"]) &&
    isset($_SESSION["role"]);

if (!$is_authenticated) {
    $_SESSION = [];

    session_destroy();

    header("Location: ../login.php?error=authentication_required");
    exit;
}