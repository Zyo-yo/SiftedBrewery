<?php

require __DIR__ . "/../includes/authenticate.php";
require __DIR__ . "/../includes/authorize.php";
require __DIR__ . "/../includes/connect.php";



/*
 * Only allow approved sorting values.
 * Never place an unvalidated GET value directly into an SQL query.
 */
$sort_options = [
    "name" => "name ASC",
    "created" => "created_at DESC",
    "updated" => "updated_at DESC"
];

$sort = $_GET["sort"] ?? "name";

if (!array_key_exists($sort, $sort_options)):
    $sort = "name";
endif;

$order_by = $sort_options[$sort];

$query = "
    SELECT
        product_id,
        name,
        description,
        price,
        category,
        image,
        available,
        created_at,
        updated_at
    FROM products
    ORDER BY $order_by
";

$statement = $db->prepare($query);
$statement->execute();

$products = $statement->fetchAll();

$success_message = $_GET["success"] ?? "";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Manage Products | The Sifted Brewery</title>

    <link rel="stylesheet" href="../styles.css">
</head>

<body>

<header class="site-header">

    <nav
        class="navigation"
        aria-label="Admin navigation"
    >

        <a
            class="brand"
            href="../index.php"
        >

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

            <a
                class="active"
                href="index.php"
            >
                Products
            </a>

            <a href="create.php">
                Add Product
            </a>
            
            <?php if (isAdmin()): ?>
                <a href="users.php">
                    Accounts
                </a>
            <?php endif; ?>   

            <a href="change-password.php">
                Change Password
            </a>
            <a
                class="admin-link"
                href="../logout.php"
            >
                Logout
            </a>

        </div>

    </nav>

</header>

<main class="admin-container">

    <section class="admin-heading">

        <div>

            <p class="eyebrow">
                Content management system
            </p>

            <h1>Manage Products</h1>

            <p class="admin-description">
                Create, edit, and remove products displayed on the public
                restaurant website.
            </p>

        </div>

        <a
            class="button button-primary"
            href="create.php"
        >
            + Add Product
        </a>

    </section>

    <?php if ($success_message === "created"): ?>

        <div class="message success-message">
            Product created successfully.
        </div>

    <?php elseif ($success_message === "updated"): ?>

        <div class="message success-message">
            Product updated successfully.
        </div>

    <?php elseif ($success_message === "deleted"): ?>

        <div class="message success-message">
            Product deleted successfully.
        </div>

    <?php elseif ($success_message === "password_changed"): ?>

    <div class="message success-message">
        Your password was changed successfully.
    </div>

    <?php endif; ?>



    <section class="admin-toolbar">

        <div>
            <strong>
                <?= count($products) ?>
                <?= count($products) === 1 ? "product" : "products" ?>
            </strong>
        </div>

        <div class="sort-controls">

            <span>Sort by:</span>

            <a
                class="<?= $sort === "name" ? "selected-sort" : "" ?>"
                href="index.php?sort=name"
            >
                Name
            </a>

            <a
                class="<?= $sort === "created" ? "selected-sort" : "" ?>"
                href="index.php?sort=created"
            >
                Created
            </a>

            <a
                class="<?= $sort === "updated" ? "selected-sort" : "" ?>"
                href="index.php?sort=updated"
            >
                Updated
            </a>

        </div>

    </section>

    <?php if (empty($products)): ?>

        <section class="empty-state">

            <h2>
                No products have been added yet.
            </h2>

            <p>
                Add your first product to begin building the restaurant menu.
            </p>

            <a
                class="button button-primary"
                href="create.php"
            >
                Create First Product
            </a>

        </section>

    <?php else: ?>

        <div class="table-wrapper">

            <table class="admin-table">

                <thead>

                    <tr>
                        <th scope="col">Image</th>
                        <th scope="col">Product</th>
                        <th scope="col">Category</th>
                        <th scope="col">Price</th>
                        <th scope="col">Availability</th>
                        <th scope="col">Created</th>
                        <th scope="col">Updated</th>
                        <th scope="col">Actions</th>
                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($products as $product): ?>

                        <tr>

                            <td class="product-image-cell">

                                <img
                                    src="../<?= htmlspecialchars(
                                        $product["image"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ) ?>"
                                    alt="<?= htmlspecialchars(
                                        $product["name"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ) ?>"
                                    class="product-thumbnail"
                                >

                            </td>

                            <td>

                                <strong>
                                    <?= htmlspecialchars(
                                        $product["name"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ) ?>
                                </strong>

                                <?php
                                $description_preview = strip_tags(
                                    $product["description"]
                                );

                                $description_preview = html_entity_decode(
                                    $description_preview,
                                    ENT_QUOTES | ENT_HTML5,
                                    "UTF-8"
                                );

                                $description_preview = preg_replace(
                                    "/\s+/u",
                                    " ",
                                    $description_preview
                                );

                                $description_preview = trim(
                                    $description_preview
                                );

                                $description_preview = mb_strimwidth(
                                    $description_preview,
                                    0,
                                    80,
                                    "..."
                                );
                                ?>

                                <p class="table-description">
                                    <?= htmlspecialchars(
                                        $description_preview,
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ) ?>
                                </p>

                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $product["category"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                ) ?>
                            </td>

                            <td>
                                $<?= number_format(
                                    (float) $product["price"],
                                    2
                                ) ?>
                            </td>

                            <td>

                                <?php if ((int) $product["available"] === 1): ?>

                                    <span class="status status-available">
                                        Available
                                    </span>

                                <?php else: ?>

                                    <span class="status status-unavailable">
                                        Unavailable
                                    </span>

                                <?php endif; ?>

                            </td>

                            <td>
                                <?= date(
                                    "M j, Y",
                                    strtotime($product["created_at"])
                                ) ?>
                            </td>

                            <td>
                                <?= date(
                                    "M j, Y",
                                    strtotime($product["updated_at"])
                                ) ?>
                            </td>

                            <td>

                                <div class="table-actions">

                                    <a
                                        class="edit-link"
                                        href="edit.php?id=<?= (int) $product["product_id"] ?>"
                                    >
                                        Edit
                                    </a>

                                    <a
                                        class="delete-link"
                                        href="delete.php?id=<?= (int) $product["product_id"] ?>"
                                    >
                                        Delete
                                    </a>

                                </div>

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