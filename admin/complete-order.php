<?php

require __DIR__ . "/../includes/authenticate.php";
require __DIR__ . "/../includes/authorize.php";
require __DIR__ . "/../includes/connect.php";
require __DIR__ . "/../includes/csrf.php";

requireAdmin();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: orders.php");
    exit;
}

if (!isValidCsrfToken($_POST["csrf_token"] ?? null)) {
    header("Location: orders.php?error=invalid_request");
    exit;
}

$order_id = filter_input(
    INPUT_POST,
    "order_id",
    FILTER_VALIDATE_INT
);

if (!$order_id) {
    header("Location: orders.php?error=invalid_order");
    exit;
}

$query = "
    SELECT
        order_id,
        status
    FROM orders
    WHERE order_id = :order_id
    LIMIT 1
";

$statement = $db->prepare($query);

$statement->execute([
    ":order_id" => $order_id
]);

$order = $statement->fetch();

if (!$order) {
    header("Location: orders.php?error=order_not_found");
    exit;
}

if ($order["status"] !== "Done") {
    $update_query = "
        UPDATE orders
        SET status = 'Done'
        WHERE order_id = :order_id
    ";

    $update_statement = $db->prepare($update_query);

    $update_statement->execute([
        ":order_id" => $order_id
    ]);
}

header(
    "Location: order-details.php?id=" .
    $order_id .
    "&success=completed"
);
exit;