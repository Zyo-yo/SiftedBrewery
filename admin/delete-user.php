<?php

require __DIR__ . "/../includes/authenticate.php";
require __DIR__ . "/../includes/authorize.php";
require __DIR__ . "/../includes/connect.php";
require __DIR__ . "/../includes/csrf.php";

requireAdmin();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: users.php");
    exit;
}

if (!isValidCsrfToken($_POST["csrf_token"] ?? null)) {
    header("Location: users.php?error=invalid_request");
    exit;
}

$user_id = filter_input(
    INPUT_POST,
    "user_id",
    FILTER_VALIDATE_INT
);

if (!$user_id) {
    header("Location: users.php?error=invalid_user");
    exit;
}

if ($user_id === (int) $_SESSION["user_id"]) {
    header("Location: users.php?error=cannot_delete_self");
    exit;
}

$query = "
    SELECT
        users.user_id,
        roles.role_name
    FROM users
    INNER JOIN roles
        ON roles.role_id = users.role_id
    WHERE users.user_id = :user_id
    LIMIT 1
";

$statement = $db->prepare($query);

$statement->execute([
    ":user_id" => $user_id
]);

$user = $statement->fetch();

if (!$user) {
    header("Location: users.php?error=user_not_found");
    exit;
}

if ($user["role_name"] === "Admin") {
    header("Location: users.php?error=cannot_delete_admin");
    exit;
}

$delete_query = "
    DELETE FROM users
    WHERE user_id = :user_id
";

$delete_statement = $db->prepare($delete_query);

$delete_statement->execute([
    ":user_id" => $user_id
]);

header("Location: users.php?success=deleted");
exit;