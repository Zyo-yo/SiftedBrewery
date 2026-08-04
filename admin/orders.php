<?php

require __DIR__ . "/../includes/authenticate.php";
require __DIR__ . "/../includes/authorize.php";
require __DIR__ . "/../includes/connect.php";

$query = "
    SELECT
        order_id,
        customer_name,
        email,
        phone,
        order_details,
        inspiration_image,
        status,
        submitted_at,
        updated_at
    FROM orders
    ORDER BY submitted_at DESC
";

$statement = $db->prepare($query);
$statement->execute();

$orders = $statement->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Manage Orders | The Sifted Brewery</title>

    <link rel="stylesheet" href="../styles.css">
</head>

<body>

<header class="site-header">

    <nav
        class="navigation"
        aria-label="Admin navigation"
    >

        <a class="brand" href="../index.php">

            <span class="brand-mark">
                <img
                    src="../images/sifted.jpg"
                    alt="The Sifted Brewery logo"
                >
            </span>

            <span class="brand-text">
                <strong>The Sifted</strong>
                <small>Brewery Admin</small>
            </span>

        </a>

        <div class="navigation-links admin-navigation">

            <a href="../index.php">
                View Website
            </a>

            <a href="index.php">
                Products
            </a>

            <a class="active" href="orders.php">
                Orders
            </a>

            <?php if (isAdmin()): ?>

                <a href="users.php">
                    Accounts
                </a>

            <?php endif; ?>

            <a href="change-password.php">
                Change Password
            </a>

            <a class="admin-link" href="../logout.php">
                Logout
            </a>

        </div>

    </nav>

</header>

<main class="admin-container">

    <section class="admin-heading">

        <div>

            <p class="eyebrow">
                Order management
            </p>

            <h1>Customer Orders</h1>

            <p class="admin-description">
                Review custom-order requests submitted by customers.
            </p>

        </div>

    </section>

    <section class="admin-toolbar">

        <div>
            <strong>
                <?= count($orders) ?>
                <?= count($orders) === 1 ? "order" : "orders" ?>
            </strong>
        </div>

    </section>

    <?php if (empty($orders)): ?>

        <section class="empty-state">

            <h2>No orders submitted</h2>

            <p>
                Customer order requests will appear here after
                submission.
            </p>

        </section>

    <?php else: ?>

        <div class="table-wrapper">

            <table class="admin-table">

                <thead>

                    <tr>
                        <th scope="col">Customer</th>
                        <th scope="col">Contact</th>
                        <th scope="col">Order</th>
                        <th scope="col">Status</th>
                        <th scope="col">Submitted</th>
                        <th scope="col">Updated</th>
                        <th scope="col">Updated</th>
                        <th scope="col">Actions</th>
                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($orders as $order): ?>

                        <?php
                        $details = preg_replace(
                            "/\s+/u",
                            " ",
                            trim($order["order_details"])
                        );

                        $details_preview = mb_strimwidth(
                            $details,
                            0,
                            120,
                            "..."
                        );
                        ?>

                        <tr>

                            <td>

                                <strong>
                                    <?= htmlspecialchars(
                                        $order["customer_name"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ) ?>
                                </strong>

                                <small>
                                    Order #<?= (int) $order["order_id"] ?>
                                </small>

                            </td>

                            <td>

                                <a href="mailto:<?= htmlspecialchars(
                                    $order["email"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                ) ?>">
                                    <?= htmlspecialchars(
                                        $order["email"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ) ?>
                                </a>

                                <?php if (!empty($order["phone"])): ?>

                                    <br>

                                    <?= htmlspecialchars(
                                        $order["phone"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ) ?>

                                <?php endif; ?>

                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $details_preview,
                                    ENT_QUOTES,
                                    "UTF-8"
                                ) ?>
                            </td>

                            <td>
                                <span class="status">
                                    <?= htmlspecialchars(
                                        $order["status"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ) ?>
                                </span>
                            </td>

                            <td>
                                <?= date(
                                    "M j, Y",
                                    strtotime($order["submitted_at"])
                                ) ?>
                            </td>

                            <td>
                                <?= date(
                                    "M j, Y",
                                    strtotime($order["updated_at"])
                                ) ?>
                            </td>

                            <td>
                                <a
                                    class="edit-link"
                                    href="order-details.php?id=<?= (int) $order["order_id"] ?>"
                                >
                                    View
                                </a>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php endif; ?>

</main>

</body>
</html>