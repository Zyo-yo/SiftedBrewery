<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$is_authenticated =
    isset($_SESSION["authenticated"]) &&
    $_SESSION["authenticated"] === true &&
    isset($_SESSION["user_id"]) &&
    isset($_SESSION["username"]) &&
    isset($_SESSION["role"]) &&
    isset($_SESSION["password_signature"]);

if (!$is_authenticated) {
    $_SESSION = [];

    session_destroy();

    header(
        "Location: ../login.php?error=authentication_required"
    );
    exit;
}

require_once __DIR__ . "/connect.php";

$query = "
    SELECT
        users.user_id,
        users.username,
        users.password,
        roles.role_name
    FROM users
    INNER JOIN roles
        ON roles.role_id = users.role_id
    WHERE users.user_id = :user_id
    LIMIT 1
";

$statement = $db->prepare($query);

$statement->execute([
    ":user_id" => (int) $_SESSION["user_id"]
]);

$current_user = $statement->fetch();

if (!$current_user) {
    $_SESSION = [];

    session_destroy();

    header(
        "Location: ../login.php?error=account_unavailable"
    );
    exit;
}

$current_password_signature = hash(
    "sha256",
    $current_user["password"]
);

if (
    !hash_equals(
        $_SESSION["password_signature"],
        $current_password_signature
    )
) {
    $_SESSION = [];

    session_destroy();

    header(
        "Location: ../login.php?error=credentials_changed"
    );
    exit;
}

$_SESSION["username"] = $current_user["username"];
$_SESSION["role"] = $current_user["role_name"];