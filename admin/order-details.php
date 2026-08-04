<?php

require __DIR__ . "/../includes/authenticate.php";
require __DIR__ . "/../includes/authorize.php";
require __DIR__ . "/../includes/connect.php";

$order_id = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);

if (!$order_id) {
    header("Location: orders.php?error=invalid_order");
    exit;
}

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

$image_path = "";

if (!empty($order["inspiration_image"])) {
    $candidate_path = str_replace(
        "\\",
        "/",
        $order["inspiration_image"]
    );

    if (
        preg_match(
            "#^images/orders/[a-zA-Z0-9._/-]+$#",
            $candidate_path
        ) &&
        is_file(__DIR__ . "/../" . $candidate_path)
    ) {
        $image_path = $candidate_path;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Order #<?= (int) $order["order_id"] ?>
        | The Sifted Brewery
    </title>

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
                Customer order
            </p>

            <h1>
                Order #<?= (int) $order["order_id"] ?>
            </h1>

            <p class="admin-description">
                Submitted
                <?= date(
                    "F j, Y \a\t g:i A",
                    strtotime($order["submitted_at"])
                ) ?>
            </p>

        </div>

        <a
            class="button button-secondary"
            href="orders.php"
        >
            Back to Orders
        </a>

    </section>

    <div class="visit-grid">

        <section class="information-card">

            <h2>Customer</h2>

            <p>
                <strong>Name:</strong><br>

                <?= htmlspecialchars(
                    $order["customer_name"],
                    ENT_QUOTES,
                    "UTF-8"
                ) ?>
            </p>

            <p>
                <strong>Email:</strong><br>

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
            </p>

            <?php if (!empty($order["phone"])): ?>

                <p>
                    <strong>Phone:</strong><br>

                    <?= htmlspecialchars(
                        $order["phone"],
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>
                </p>

            <?php endif; ?>

        </section>

        <section class="information-card">

            <h2>Status</h2>

            <p>
                <span class="status">
                    <?= htmlspecialchars(
                        $order["status"],
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>
                </span>
            </p>

            <p>
                <strong>Last updated:</strong><br>

                <?= date(
                    "F j, Y \a\t g:i A",
                    strtotime($order["updated_at"])
                ) ?>
            </p>

        </section>

    </div>

    <section class="information-card">

        <h2>Order Details</h2>

        <p>
            <?= nl2br(
                htmlspecialchars(
                    $order["order_details"],
                    ENT_QUOTES,
                    "UTF-8"
                )
            ) ?>
        </p>

    </section>

    <?php if ($image_path !== ""): ?>

        <section class="information-card">

            <h2>Inspiration Image</h2>

            <p>
                <a
                    href="../<?= htmlspecialchars(
                        $image_path,
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>"
                    target="_blank"
                    rel="noopener"
                >
                    View full-size image
                </a>
            </p>

            <img
                src="../<?= htmlspecialchars(
                    $image_path,
                    ENT_QUOTES,
                    "UTF-8"
                ) ?>"
                alt="Customer inspiration for order #<?= (int) $order["order_id"] ?>"
                class="product-detail-image"
            >

        </section>

    <?php endif; ?>

</main>

</body>
</html>