<?php

require __DIR__ . "/../includes/authenticate.php";
require __DIR__ . "/../includes/connect.php";
require __DIR__ . "/../includes/sanitize_description.php";

$name = "";
$description = "";
$price = "";
$category = "";
$available = 1;
$image = "images/products/no-image.png";

$errors = [];

$categories = [
    "Pastries",
    "Cakes",
    "Drinks",
    "Meals"
];

if ($_SERVER["REQUEST_METHOD"] === "POST"):

    $name = trim($_POST["name"] ?? "");
    $submitted_description =
    $_POST["description"] ?? "";
    $description = sanitizeDescription($submitted_description);
    $description_text = getDescriptionText($description);
    $price = trim($_POST["price"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $available = isset($_POST["available"]) ? 1 : 0;

    /*
     * Product name validation
     */
    if ($name === ""):

        $errors[] = "The product name is required.";

    elseif (mb_strlen($name) > 150):

        $errors[] = "The product name cannot be longer than 150 characters.";

    endif;

    /*
     * Description validation
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
     * Price validation
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
     * Category validation
     */
    if (!in_array($category, $categories, true)):

        $errors[] = "Please select a valid product category.";

    endif;

    /*
     * Default image path
     */
    $image = "images/products/no-image.png";

    /*
     * Image upload validation
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
             * Determine the actual MIME type of the uploaded file.
             */
            $allowed_types = [
                "image/jpeg" => "jpg",
                "image/png" => "png",
                "image/webp" => "webp"
            ];

            $file_info = finfo_open(FILEINFO_MIME_TYPE);

            if ($file_info === false):

                $errors[] = "The uploaded image could not be validated.";

            else:

                $mime_type = finfo_file(
                    $file_info,
                    $_FILES["image"]["tmp_name"]
                );

                finfo_close($file_info);

                if (!array_key_exists($mime_type, $allowed_types)):

                    $errors[] = "Please upload a JPG, PNG, or WEBP image.";

                else:

                    $upload_directory =
                        __DIR__ . "/../images/products/";

                    /*
                     * Create the upload directory if it does not exist.
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

                        $extension = $allowed_types[$mime_type];

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

                            $image =
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
     * Insert the product when validation succeeds.
     */
    if (empty($errors)):

        $query = "
            INSERT INTO products
            (
                name,
                description,
                price,
                category,
                image,
                available
            )
            VALUES
            (
                :name,
                :description,
                :price,
                :category,
                :image,
                :available
            )
        ";

        $statement = $db->prepare($query);

        $statement->bindValue(
            ":name",
            $name,
            PDO::PARAM_STR
        );

        $statement->bindValue(
            ":description",
            $description,
            PDO::PARAM_STR
        );

        $statement->bindValue(
            ":price",
            number_format(
                (float) $price,
                2,
                ".",
                ""
            ),
            PDO::PARAM_STR
        );

        $statement->bindValue(
            ":category",
            $category,
            PDO::PARAM_STR
        );

        $statement->bindValue(
            ":image",
            $image,
            PDO::PARAM_STR
        );

        $statement->bindValue(
            ":available",
            $available,
            PDO::PARAM_INT
        );

        $statement->execute();

        header("Location: index.php?success=created");
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

    <title>Add Product | The Sifted Brewery</title>

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

            <a
                class="active"
                href="create.php"
            >
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

            <h1>Add a New Product</h1>

            <p>
                Enter the product information that will appear on the public
                restaurant website.
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
        action="create.php"
        enctype="multipart/form-data"
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
                minlength="10"
                required
            ><?= htmlspecialchars(
                $description,
                ENT_QUOTES,
                "UTF-8"
            ) ?></textarea>

            <small>
                Describe the product, flavour, ingredients, or serving details.
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
                            $price,
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

                    <?php foreach ($categories as $category_option): ?>

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

            <label for="image">
                Product Image
            </label>

            <input
                type="file"
                id="image"
                name="image"
                accept=".jpg,.jpeg,.png,.webp"
            >

            <small>
                Upload a JPG, PNG, or WEBP image. Maximum size: 2 MB.
                A placeholder will be used when no image is selected.
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
                    Uncheck this option to hide the product from the public
                    website.
                </small>

            </div>

        </div>

        <div class="form-actions">

            <button
                class="button button-primary"
                type="submit"
            >
                Create Product
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