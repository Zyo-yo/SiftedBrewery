<?php

require __DIR__ . "/includes/connect.php";

/*
 * Validate the product ID from the URL.
 * Example: product.php?id=5
 */
$product_id = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);

$product = false;

/*
 * Retrieve the requested product only when the ID is valid.
 *
 * Products marked as unavailable must not be accessible from
 * the public product page.
 */
if ($product_id):

    $query = "
        SELECT
            product_id,
            name,
            description,
            price,
            category,
            image
        FROM products
        WHERE product_id = :product_id
          AND available = 1
    ";

    $statement = $db->prepare($query);

    $statement->bindValue(
        ":product_id",
        $product_id,
        PDO::PARAM_INT
    );

    $statement->execute();

    $product = $statement->fetch(PDO::FETCH_ASSOC);

endif;

/*
 * Set the page title and HTTP response status.
 */
if ($product):

    $page_title =
        $product["name"] .
        " | The Sifted Brewery";

else:

    http_response_code(404);

    $page_title =
        "Product Not Found | The Sifted Brewery";

endif;

$current_page = "menu";

include __DIR__ . "/includes/header.php";
include __DIR__ . "/includes/nav.php";
?>

<main>

    <?php if ($product): ?>

        <?php
        /*
         * Use the default image when the product does not have
         * an uploaded image.
         */
        $product_image =
            $product["image"]
            ?: "images/products/no-image.png";

        /*
         * Link back to the product's category on menu.php.
         */
        $category_id =
            strtolower($product["category"]);

        $category_url =
            "menu.php#" .
            rawurlencode($category_id);
        ?>

        <section class="product-detail-section">

            <div class="product-detail-navigation">

                <a
                    class="back-link"
                    href="<?= htmlspecialchars(
                        $category_url,
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>"
                >
                    ← Back to
                    <?= htmlspecialchars(
                        $product["category"],
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>
                </a>

            </div>

            <article class="product-detail">

                <div class="product-detail-image">

                    <img
                        src="<?= htmlspecialchars(
                            $product_image,
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

                <div class="product-detail-content">

                    <p class="eyebrow">

                        <?= htmlspecialchars(
                            $product["category"],
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>

                    </p>

                    <h1>

                        <?= htmlspecialchars(
                            $product["name"],
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>

                    </h1>

                    <p class="product-detail-price">

                        $<?= number_format(
                            (float) $product["price"],
                            2
                        ) ?>

                    </p>

                    <div class="product-detail-divider"></div>

                    <div class="product-detail-description">

                        <?= $product["description"] ?>

                    </div>

                    <div class="product-detail-actions">

                        <a
                            class="button button-primary"
                            href="<?= htmlspecialchars(
                                $category_url,
                                ENT_QUOTES,
                                "UTF-8"
                            ) ?>"
                        >
                            Return to Menu
                        </a>

                        <a
                            class="button button-secondary"
                            href="index.php#contact"
                        >
                            Contact Us
                        </a>

                    </div>

                </div>

            </article>

        </section>

    <?php else: ?>

        <section class="product-not-found">

            <div class="product-not-found-content">

                <p class="eyebrow">
                    Product unavailable
                </p>

                <h1>Product Not Found</h1>

                <p>
                    The product you requested does not exist or is
                    not currently available on our public menu.
                </p>

                <div class="product-not-found-actions">

                    <a
                        class="button button-primary"
                        href="menu.php"
                    >
                        Browse Our Menu
                    </a>

                    <a
                        class="button button-secondary"
                        href="index.php"
                    >
                        Return Home
                    </a>

                </div>

            </div>

        </section>

    <?php endif; ?>

</main>

<?php include __DIR__ . "/includes/footer.php"; ?>