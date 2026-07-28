<?php

require __DIR__ . "/../includes/authenticate.php";
require __DIR__ . "/../includes/connect.php";
require __DIR__ . "/../includes/sanitize_description.php";

/*
 * Categories must match the options used in create.php.
 */
$categories = [
    "Pastries",
    "Cakes",
    "Drinks",
    "Meals"
];

$default_image = "images/products/no-image.png";
$errors = [];

/*
 * Validate the product ID from the URL.
 * Example: edit.php?id=3
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
 * Retrieve the selected product.
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
 * Redirect if the product does not exist.
 */
if (!$product):

    header("Location: index.php");
    exit;

endif;

/*
 * Store the existing product information.
 */
$name = $product["name"];
$description = $product["description"];
$price = $product["price"];
$category = $product["category"];
$available = (int) $product["available"];
$current_image = $product["image"] ?: $default_image;

/*
 * This variable stores a newly uploaded image path.
 * It remains null when no replacement image is uploaded.
 */
$new_image = null;

/*
 * Process the submitted form.
 */
if ($_SERVER["REQUEST_METHOD"] === "POST"):

    /*
     * Confirm that the submitted product ID matches
     * the product being edited.
     */
    $submitted_product_id = filter_input(
        INPUT_POST,
        "product_id",
        FILTER_VALIDATE_INT
    );

    if (
        !$submitted_product_id ||
        $submitted_product_id !== $product_id
    ):

        $errors[] = "The product submission is invalid.";

    endif;

    /*
     * Retrieve submitted values.
     */
    $name = trim($_POST["name"] ?? "");
    $submitted_description =
    $_POST["description"] ?? "";
    $description = sanitizeDescription($submitted_description);
    $description_text = getDescriptionText($description);
    $price = trim($_POST["price"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $available = isset($_POST["available"]) ? 1 : 0;

    /*
     * Product name validation.
     */
    if ($name === ""):

        $errors[] = "The product name is required.";

    elseif (mb_strlen($name) > 150):

        $errors[] =
            "The product name cannot be longer than 150 characters.";

    endif;

    /*
     * Description validation.
     */
    if ($description_text === ""):

        $errors[] =
            "The product description is required.";

    elseif (mb_strlen($description_text) < 10):

        $errors[] =
            "The product description must be at least 10 characters.";

    elseif (mb_strlen($description_text) > 2000):

        $errors[] =
            "The product description cannot exceed 2,000 characters.";

    endif;

    /*
     * Price validation.
     */
    if ($price === ""):

        $errors[] = "The product price is required.";

    elseif (!is_numeric($price)):

        $errors[] = "The product price must be a number.";

    elseif ((float) $price < 0):

        $errors[] = "The product price cannot be negative.";

    elseif ((float) $price > 99999999.99):

        $errors[] = "The product price is too large.";

    endif;

    /*
     * Category validation.
     */
    if (!in_array($category, $categories, true)):

        $errors[] = "Please select a valid product category.";

    endif;

    /*
     * Validate and upload a replacement image.
     *
     * When no new image is selected, the current image
     * remains unchanged.
     */
    if (
        isset($_FILES["image"]) &&
        $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE
    ):

        if ($_FILES["image"]["error"] !== UPLOAD_ERR_OK):

            $errors[] = "The image could not be uploaded.";

        elseif ($_FILES["image"]["size"] > 2 * 1024 * 1024):

            $errors[] = "The image must be 2 MB or smaller.";

        else:

            /*
             * Check the actual MIME type instead of trusting
             * the file extension supplied by the user.
             */
            $allowed_types = [
                "image/jpeg" => "jpg",
                "image/png" => "png",
                "image/webp" => "webp"
            ];

            $file_info = finfo_open(FILEINFO_MIME_TYPE);

            if ($file_info === false):

                $errors[] =
                    "The uploaded image could not be validated.";

            else:

                $mime_type = finfo_file(
                    $file_info,
                    $_FILES["image"]["tmp_name"]
                );

                finfo_close($file_info);

                if (!array_key_exists(
                    $mime_type,
                    $allowed_types
                )):

                    $errors[] =
                        "Please upload a JPG, PNG, or WEBP image.";

                else:

                    $upload_directory =
                        __DIR__ . "/../images/products/";

                    /*
                     * Create the upload directory if needed.
                     */
                    if (!is_dir($upload_directory)):

                        mkdir(
                            $upload_directory,
                            0755,
                            true
                        );

                    endif;

                    if (!is_dir($upload_directory)):

                        $errors[] =
                            "The product image directory could not be created.";

                    else:

                        $extension =
                            $allowed_types[$mime_type];

                        $filename = uniqid(
                            "product_",
                            true
                        ) . "." . $extension;

                        $destination =
                            $upload_directory . $filename;

                        if (
                            move_uploaded_file(
                                $_FILES["image"]["tmp_name"],
                                $destination
                            )
                        ):

                            $new_image =
                                "images/products/" . $filename;

                        else:

                            $errors[] =
                                "The image could not be saved.";

                        endif;

                    endif;

                endif;

            endif;

        endif;

    endif;

    /*
     * Update the product when validation succeeds.
     */
    if (empty($errors)):

        /*
         * Keep the current image unless a new one was uploaded.
         */
        $updated_image =
            $new_image !== null
                ? $new_image
                : $current_image;

        $update_query = "
            UPDATE products
            SET
                name = :name,
                description = :description,
                price = :price,
                category = :category,
                image = :image,
                available = :available,
                updated_at = NOW()
            WHERE product_id = :product_id
        ";

        $update_statement = $db->prepare($update_query);

        $update_statement->bindValue(
            ":name",
            $name,
            PDO::PARAM_STR
        );

        $update_statement->bindValue(
            ":description",
            $description,
            PDO::PARAM_STR
        );

        $update_statement->bindValue(
            ":price",
            number_format(
                (float) $price,
                2,
                ".",
                ""
            ),
            PDO::PARAM_STR
        );

        $update_statement->bindValue(
            ":category",
            $category,
            PDO::PARAM_STR
        );

        $update_statement->bindValue(
            ":image",
            $updated_image,
            PDO::PARAM_STR
        );

        $update_statement->bindValue(
            ":available",
            $available,
            PDO::PARAM_INT
        );

        $update_statement->bindValue(
            ":product_id",
            $product_id,
            PDO::PARAM_INT
        );

        $update_statement->execute();

        /*
         * Delete the old image after the database update succeeds.
         *
         * Never delete the default placeholder image.
         */
        if (
            $new_image !== null &&
            $current_image !== $default_image
        ):

            $old_image_path =
                __DIR__ . "/../" . $current_image;

            if (
                is_file($old_image_path) &&
                file_exists($old_image_path)
            ):

                unlink($old_image_path);

            endif;

        endif;

        header("Location: index.php?success=updated");
        exit;

    endif;

    /*
     * If an image was successfully uploaded but another
     * validation error prevented the database update,
     * delete the unused uploaded image.
     */
    if (
        !empty($errors) &&
        $new_image !== null
    ):

        $unused_image_path =
            __DIR__ . "/../" . $new_image;

        if (
            is_file($unused_image_path) &&
            file_exists($unused_image_path)
        ):

            unlink($unused_image_path);

        endif;

        $new_image = null;

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

    <title>Edit Product | The Sifted Brewery</title>

    <link rel="stylesheet" href="../styles.css">
</head>
<script
    src="https://cdn.jsdelivr.net/npm/tinymce@8.5.0/tinymce.min.js"
    referrerpolicy="origin"
></script>

<script>
    tinymce.init({
        selector: ".wysiwyg-editor",
        license_key: "gpl",
        height: 360,
        menubar: false,
        branding: false,
        promotion: false,

        plugins: [
            "lists",
            "link",
            "autolink",
            "wordcount"
        ],

        toolbar:
            "undo redo | blocks | bold italic | " +
            "bullist numlist | blockquote | link unlink | " +
            "removeformat",

        block_formats:
            "Paragraph=p;" +
            "Heading 2=h2;" +
            "Heading 3=h3;" +
            "Heading 4=h4",

        valid_elements:
            "p,br,strong/b,em/i,h2,h3,h4," +
            "ul,ol,li,blockquote," +
            "a[href|target|rel]",

        forced_root_block: "p",

        link_default_target: "_blank",

        link_assume_external_targets: "https",

        content_style:
            "body {" +
                "font-family: Arial, Helvetica, sans-serif;" +
                "font-size: 16px;" +
                "line-height: 1.6;" +
                "color: #3c2a21;" +
                "padding: 12px;" +
            "}" +
            "h2, h3, h4 {" +
                "font-family: Georgia, 'Times New Roman', serif;" +
            "}",

        setup: function (editor) {
            editor.on("change input undo redo", function () {
                editor.save();
            });
        }
    });
</script>


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

            <h1>Edit Product</h1>

            <p>
                Update the product information that appears on the
                public restaurant website.
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

    <form
        method="post"
        action="edit.php?id=<?= $product_id ?>"
        enctype="multipart/form-data"
    >

        <input
            type="hidden"
            name="product_id"
            value="<?= $product_id ?>"
        >

        <div class="form-group">

            <label for="name">
                Product Name
                <span class="required">*</span>
            </label>

            <input
                type="text"
                id="name"
                name="name"
                maxlength="150"
                value="<?= htmlspecialchars(
                    $name,
                    ENT_QUOTES,
                    "UTF-8"
                ) ?>"
                required
            >

            <small>
                Example: Strawberry Cream Cake
            </small>

        </div>

        <div class="form-group">

            <label for="description">
                Description
                <span class="required">*</span>
            </label>

            <textarea
                id="description"
                name="description"
                class="wysiwyg-editor"
                required
            ><?= htmlspecialchars(
                $description,
                ENT_QUOTES,
                "UTF-8"
            ) ?></textarea>

            <small>
                Describe the product, flavour, ingredients, or
                serving details.
            </small>

        </div>

        <div class="form-row">

            <div class="form-group">

                <label for="price">
                    Price
                    <span class="required">*</span>
                </label>

                <div class="price-input">

                    <span>$</span>

                    <input
                        type="number"
                        id="price"
                        name="price"
                        min="0"
                        max="99999999.99"
                        step="0.01"
                        value="<?= htmlspecialchars(
                            (string) $price,
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>"
                        required
                    >

                </div>

            </div>

            <div class="form-group">

                <label for="category">
                    Category
                    <span class="required">*</span>
                </label>

                <select
                    id="category"
                    name="category"
                    required
                >

                    <option value="">
                        Select a category
                    </option>

                    <?php foreach (
                        $categories as $category_option
                    ): ?>

                        <option
                            value="<?= htmlspecialchars(
                                $category_option,
                                ENT_QUOTES,
                                "UTF-8"
                            ) ?>"
                            <?= $category === $category_option
                                ? "selected"
                                : "" ?>
                        >
                            <?= htmlspecialchars(
                                $category_option,
                                ENT_QUOTES,
                                "UTF-8"
                            ) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

        </div>

        <div class="form-group">

            <label>
                Current Product Image
            </label>

            <div class="current-product-image">

                <img
                    class="product-thumbnail"
                    src="../<?= htmlspecialchars(
                        $current_image,
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>"
                    alt="<?= htmlspecialchars(
                        $name,
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>"
                >

            </div>

        </div>

        <div class="form-group">

            <label for="image">
                Replace Product Image
            </label>

            <input
                type="file"
                id="image"
                name="image"
                accept=".jpg,.jpeg,.png,.webp"
            >

            <small>
                Upload a JPG, PNG, or WEBP image. Maximum size:
                2 MB. Leave this field empty to keep the current
                product image.
            </small>

        </div>

        <div class="form-group checkbox-group">

            <input
                type="checkbox"
                id="available"
                name="available"
                value="1"
                <?= $available === 1 ? "checked" : "" ?>
            >

            <div>

                <label for="available">
                    Available for customers
                </label>

                <small>
                    Uncheck this option to hide the product from
                    the public website.
                </small>

            </div>

        </div>

        <div class="form-actions">

            <button
                class="button button-primary"
                type="submit"
            >
                Update Product
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