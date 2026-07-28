<?php

require __DIR__ . "/includes/connect.php";

$page_title = "Menu | The Sifted Brewery";
$current_page = "menu";

/*
 * Keep the public menu categories in a consistent order.
 */
$categories = [
    "Pastries",
    "Cakes",
    "Drinks",
    "Meals"
];

/*
 * Retrieve only products marked as available.
 */
$query = "
    SELECT
        product_id,
        name,
        description,
        price,
        category,
        image
    FROM products
    WHERE available = 1
    ORDER BY category ASC, name ASC
";

$statement = $db->prepare($query);
$statement->execute();

$products = $statement->fetchAll(PDO::FETCH_ASSOC);

/*
 * Group products by category.
 */
$products_by_category = [];

foreach ($categories as $category):

    $products_by_category[$category] = [];

endforeach;

foreach ($products as $product):

    $product_category = $product["category"];

    if (array_key_exists(
        $product_category,
        $products_by_category
    )):

        $products_by_category[$product_category][] = $product;

    endif;

endforeach;

include __DIR__ . "/includes/header.php";
include __DIR__ . "/includes/nav.php";
?>

<main>

    <section class="menu-hero">

        <div class="menu-hero-content">

            <p class="eyebrow">
                Freshly prepared
            </p>

            <h1>Explore Our Menu</h1>

            <p>
                Browse our selection of pastries, cakes, specialty
                drinks, and comforting meals prepared with care.
            </p>

        </div>

    </section>

    <nav
        class="category-navigation"
        aria-label="Menu categories"
    >

        <div class="category-navigation-links">

            <?php foreach ($categories as $category): ?>

                <a href="#<?= strtolower($category) ?>">

                    <?= htmlspecialchars(
                        $category,
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>

                </a>

            <?php endforeach; ?>

        </div>

    </nav>

    <section class="content-section menu-content">

        <?php if (!empty($products)): ?>

            <?php foreach ($categories as $category): ?>

                <?php
                $category_products =
                    $products_by_category[$category];
                ?>

                <section
                    class="menu-category-section"
                    id="<?= strtolower($category) ?>"
                >

                    <div class="menu-category-heading">

                        <div>

                            <p class="eyebrow">
                                The Sifted Brewery
                            </p>

                            <h2>

                                <?= htmlspecialchars(
                                    $category,
                                    ENT_QUOTES,
                                    "UTF-8"
                                ) ?>

                            </h2>

                        </div>

                        <p class="category-product-count">

                            <?= count($category_products) ?>

                            <?= count($category_products) === 1
                                ? "product"
                                : "products" ?>

                        </p>

                    </div>

                    <?php if (!empty($category_products)): ?>

                        <div class="product-grid">

                            <?php foreach (
                                $category_products as $product
                            ): ?>

                                <?php
                                /*
                                 * Use the default placeholder if the
                                 * product does not have an image.
                                 */
                                $product_image =
                                    $product["image"]
                                    ?: "images/products/no-image.png";

                                /*
                                 * Create a plain-text description
                                 * preview for the menu card.
                                 *
                                 * The complete formatted description
                                 * will appear on product.php.
                                 */
                                $description_text = strip_tags(
                                    $product["description"]
                                );

                                $description_text =
                                    html_entity_decode(
                                        $description_text,
                                        ENT_QUOTES | ENT_HTML5,
                                        "UTF-8"
                                    );

                                $description_text =
                                    preg_replace(
                                        "/\s+/u",
                                        " ",
                                        $description_text
                                    );

                                $description_text =
                                    trim($description_text);

                                $description_preview =
                                    mb_strimwidth(
                                        $description_text,
                                        0,
                                        140,
                                        "..."
                                    );

                                /*
                                 * Build the detail-page link once and
                                 * reuse it throughout the product card.
                                 */
                                $product_url =
                                    "product.php?id=" .
                                    (int) $product["product_id"];
                                ?>

                                <article class="public-product-card">

                                    <div class="public-product-image">

                                        <a
                                            href="<?= $product_url ?>"
                                            aria-label="View <?= htmlspecialchars(
                                                $product["name"],
                                                ENT_QUOTES,
                                                "UTF-8"
                                            ) ?>"
                                        >

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
                                                loading="lazy"
                                            >

                                        </a>

                                    </div>

                                    <div class="public-product-content">

                                        <p class="product-category">

                                            <?= htmlspecialchars(
                                                $product["category"],
                                                ENT_QUOTES,
                                                "UTF-8"
                                            ) ?>

                                        </p>

                                        <h3>

                                            <a href="<?= $product_url ?>">

                                                <?= htmlspecialchars(
                                                    $product["name"],
                                                    ENT_QUOTES,
                                                    "UTF-8"
                                                ) ?>

                                            </a>

                                        </h3>

                                        <p class="product-preview">

                                            <?= htmlspecialchars(
                                                $description_preview,
                                                ENT_QUOTES,
                                                "UTF-8"
                                            ) ?>

                                        </p>

                                        <div class="product-card-footer">

                                            <p class="public-product-price">

                                                $<?= number_format(
                                                    (float) $product["price"],
                                                    2
                                                ) ?>

                                            </p>

                                            <a
                                                class="text-link"
                                                href="<?= $product_url ?>"
                                            >
                                                View Product
                                            </a>

                                        </div>

                                    </div>

                                </article>

                            <?php endforeach; ?>

                        </div>

                    <?php else: ?>

                        <div class="empty-category-message">

                            <p>
                                There are currently no available
                                <?= strtolower(
                                    htmlspecialchars(
                                        $category,
                                        ENT_QUOTES,
                                        "UTF-8"
                                    )
                                ) ?>.
                            </p>

                        </div>

                    <?php endif; ?>

                </section>

            <?php endforeach; ?>

        <?php else: ?>

            <div class="empty-menu-message">

                <h2>Our menu is being prepared.</h2>

                <p>
                    There are currently no products available.
                    Please check back soon.
                </p>

                <a
                    class="button button-secondary"
                    href="index.php"
                >
                    Return Home
                </a>

            </div>

        <?php endif; ?>

    </section>

</main>

<?php include __DIR__ . "/includes/footer.php"; ?>