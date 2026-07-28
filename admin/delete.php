<?php

require __DIR__ . "/../includes/authenticate.php";
require __DIR__ . "/../includes/connect.php";

$default_image = "images/products/no-image.png";
$errors = [];

/*
 * Validate the product ID from the URL.
 * Example: delete.php?id=3
 */
$product_id = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);

if (!$product_id):

    header("Location: index.php");
    exit;

endif;

/*
 * Retrieve the selected product so the administrator
 * can review it before confirming deletion.
 */
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
    WHERE product_id = :product_id
";

$statement = $db->prepare($query);

$statement->bindValue(
    ":product_id",
    $product_id,
    PDO::PARAM_INT
);

$statement->execute();

$product = $statement->fetch(PDO::FETCH_ASSOC);

/*
 * Return to the dashboard if the product does not exist.
 */
if (!$product):

    header("Location: index.php");
    exit;

endif;

/*
 * Process the confirmed deletion.
 */
if ($_SERVER["REQUEST_METHOD"] === "POST"):

    $submitted_product_id = filter_input(
        INPUT_POST,
        "product_id",
        FILTER_VALIDATE_INT
    );

    /*
     * Confirm that the hidden product ID matches the
     * product ID in the page URL.
     */
    if (
        !$submitted_product_id ||
        $submitted_product_id !== $product_id
    ):

        $errors[] = "The product deletion request is invalid.";

    endif;

    /*
     * The confirmation checkbox must be selected.
     */
    if (!isset($_POST["confirm_delete"])):

        $errors[] =
            "Please confirm that you want to permanently delete this product.";

    endif;

    if (empty($errors)):

        /*
         * Store the image path before deleting the database record.
         */
        $product_image =
            $product["image"] ?: $default_image;

        /*
         * Delete the selected product using a prepared statement.
         */
        $delete_query = "
            DELETE FROM products
            WHERE product_id = :product_id
        ";

        $delete_statement = $db->prepare($delete_query);

        $delete_statement->bindValue(
            ":product_id",
            $product_id,
            PDO::PARAM_INT
        );

        $delete_statement->execute();

        /*
         * Delete the uploaded image after the database record
         * has been successfully removed.
         *
         * Never delete the shared default placeholder image.
         */
        if (
            $delete_statement->rowCount() === 1 &&
            $product_image !== $default_image
        ):

            $image_path =
                __DIR__ . "/../" . $product_image;

            if (
                is_file($image_path) &&
                file_exists($image_path)
            ):

                unlink($image_path);

            endif;

        endif;

        header("Location: index.php?success=deleted");
        exit;

    endif;

endif;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Delete Product | The Sifted Brewery</title>

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

                <small>
                    Brewery Admin
                </small>

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

            <a
                class="admin-link"
                href="../logout.php"
            >
                Logout
            </a>

        </div>

    </nav>

</header>

<main class="form-container">

    <div class="form-heading">

        <div>

            <p class="eyebrow">
                Product management
            </p>

            <h1>Delete Product</h1>

            <p>
                Review the product below before permanently
                removing it from the website.
            </p>

        </div>

        <a
            class="back-link"
            href="index.php"
        >
            Back to Products
        </a>

    </div>

    <?php if (!empty($errors)): ?>

        <section
            class="message error-message"
            aria-labelledby="error-heading"
        >

            <h2 id="error-heading">
                Please correct the following:
            </h2>

            <ul>

                <?php foreach ($errors as $error): ?>

                    <li>
                        <?= htmlspecialchars(
                            $error,
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>
                    </li>

                <?php endforeach; ?>

            </ul>

        </section>

    <?php endif; ?>

    <section class="delete-warning">

        <h2>
            Are you sure you want to delete this product?
        </h2>

        <p>
            This action cannot be undone. The product will be
            removed from the database and its uploaded image will
            also be deleted.
        </p>

    </section>

    <section class="delete-product-card">

        <div class="delete-product-image">

            <img
                src="../<?= htmlspecialchars(
                    $product["image"] ?: $default_image,
                    ENT_QUOTES,
                    "UTF-8"
                ) ?>"
                alt="<?= htmlspecialchars(
                    $product["name"],
                    ENT_QUOTES,
                    "UTF-8"
                ) ?>"
            >

        </div>

        <div class="delete-product-details">

            <p class="eyebrow">
                Product to be deleted
            </p>

            <h2>
                <?= htmlspecialchars(
                    $product["name"],
                    ENT_QUOTES,
                    "UTF-8"
                ) ?>
            </h2>

            <p class="delete-product-description">
                <?= htmlspecialchars(
                    $product["description"],
                    ENT_QUOTES,
                    "UTF-8"
                ) ?>
            </p>

            <dl class="delete-product-information">

                <div>

                    <dt>
                        Category
                    </dt>

                    <dd>
                        <?= htmlspecialchars(
                            $product["category"],
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>
                    </dd>

                </div>

                <div>

                    <dt>
                        Price
                    </dt>

                    <dd>
                        $<?= number_format(
                            (float) $product["price"],
                            2
                        ) ?>
                    </dd>

                </div>

                <div>

                    <dt>
                        Availability
                    </dt>

                    <dd>

                        <?php if (
                            (int) $product["available"] === 1
                        ): ?>

                            <span class="status status-available">
                                Available
                            </span>

                        <?php else: ?>

                            <span class="status status-unavailable">
                                Unavailable
                            </span>

                        <?php endif; ?>

                    </dd>

                </div>

            </dl>

        </div>

    </section>

    <form
        method="post"
        action="delete.php?id=<?= $product_id ?>"
    >

        <input
            type="hidden"
            name="product_id"
            value="<?= $product_id ?>"
        >

        <div class="form-group checkbox-group delete-confirmation">

            <input
                type="checkbox"
                id="confirm_delete"
                name="confirm_delete"
                value="1"
            >

            <div>

                <label for="confirm_delete">
                    I understand that this product will be
                    permanently deleted.
                </label>

                <small>
                    This action cannot be reversed after you select
                    Delete Product.
                </small>

            </div>

        </div>

        <div class="form-actions">

            <button
                class="button delete-button"
                type="submit"
            >
                Delete Product
            </button>

            <a
                class="button button-secondary"
                href="index.php"
            >
                Cancel
            </a>

        </div>

    </form>

</main>

</body>
</html>